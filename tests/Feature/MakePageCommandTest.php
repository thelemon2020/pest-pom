<?php

declare(strict_types=1);

use Thelemon2020\PestPages\Config;

afterEach(function () {
    $dir = Config::absolutePath();

    if (is_dir($dir)) {
        foreach (glob($dir.DIRECTORY_SEPARATOR.'*.php') ?: [] as $file) {
            unlink($file);
        }
        rmdir($dir);
    }
});

it('creates a page file in the configured directory', function () {
    $this->artisan('pest:page', ['name' => 'Login'])->assertSuccessful();

    expect(Config::absolutePath().DIRECTORY_SEPARATOR.'LoginPage.php')->toBeFile();
});

it('appends the Page suffix when it is missing', function () {
    $this->artisan('pest:page', ['name' => 'Login'])->assertSuccessful();

    expect(Config::absolutePath().DIRECTORY_SEPARATOR.'LoginPage.php')->toBeFile();
});

it('does not double the Page suffix', function () {
    $this->artisan('pest:page', ['name' => 'LoginPage'])->assertSuccessful();

    expect(Config::absolutePath().DIRECTORY_SEPARATOR.'LoginPage.php')->toBeFile();
    expect(Config::absolutePath().DIRECTORY_SEPARATOR.'LoginPagePage.php')->not->toBeFile();
});

it('fails when the file already exists', function () {
    $this->artisan('pest:page', ['name' => 'Login'])->assertSuccessful();
    $this->artisan('pest:page', ['name' => 'Login'])->assertFailed();
});

it('creates the pages directory when it does not exist', function () {
    $dir = Config::absolutePath();
    expect($dir)->not->toBeDirectory();

    $this->artisan('pest:page', ['name' => 'Login'])->assertSuccessful();

    expect($dir)->toBeDirectory();
});

it('generates a class that extends Page', function () {
    $this->artisan('pest:page', ['name' => 'Login'])->assertSuccessful();

    $content = file_get_contents(Config::absolutePath().DIRECTORY_SEPARATOR.'LoginPage.php');

    expect($content)->toContain('extends Page');
});

it('generates a url method stub', function () {
    $this->artisan('pest:page', ['name' => 'Login'])->assertSuccessful();

    $content = file_get_contents(Config::absolutePath().DIRECTORY_SEPARATOR.'LoginPage.php');

    expect($content)->toContain('public static function url(): string');
});

it('includes requested concerns in the generated class', function () {
    $this->artisan('pest:page', ['name' => 'Register', '--concerns' => 'forms,alerts'])->assertSuccessful();

    $content = file_get_contents(Config::absolutePath().DIRECTORY_SEPARATOR.'RegisterPage.php');

    expect($content)
        ->toContain('use InteractsWithForms')
        ->toContain('use InteractsWithAlerts')
        ->toContain('use Thelemon2020\\PestPages\\Concerns\\InteractsWithForms')
        ->toContain('use Thelemon2020\\PestPages\\Concerns\\InteractsWithAlerts');
});

it('warns about unknown concerns and skips them', function () {
    $this->artisan('pest:page', ['name' => 'Login', '--concerns' => 'forms,unknown'])
        ->assertSuccessful()
        ->expectsOutputToContain('Unknown concern [unknown]');

    $content = file_get_contents(Config::absolutePath().DIRECTORY_SEPARATOR.'LoginPage.php');

    expect($content)
        ->toContain('use InteractsWithForms')
        ->not->toContain('unknown');
});

it('derives the namespace from the project PSR-4 map', function () {
    file_put_contents(base_path('composer.json'), json_encode([
        'autoload-dev' => ['psr-4' => ['Tests\\' => 'tests/']],
    ]));

    $this->artisan('pest:page', ['name' => 'Login'])->assertSuccessful();

    $content = file_get_contents(Config::absolutePath().DIRECTORY_SEPARATOR.'LoginPage.php');

    expect($content)->toContain('namespace Tests\\Browser\\Pages;');
});

it('falls back to Tests\\Browser\\Pages when no PSR-4 map matches', function () {
    file_put_contents(base_path('composer.json'), json_encode([]));

    $this->artisan('pest:page', ['name' => 'Login'])->assertSuccessful();

    $content = file_get_contents(Config::absolutePath().DIRECTORY_SEPARATOR.'LoginPage.php');

    expect($content)->toContain('namespace Tests\\Browser\\Pages;');
});

it('uses the configured path when generating files', function () {
    config(['pest-pages.path' => 'tests/Custom/Pages']);

    $this->artisan('pest:page', ['name' => 'Login'])->assertSuccessful();

    $customDir  = base_path('tests/Custom/Pages');
    $customFile = $customDir.DIRECTORY_SEPARATOR.'LoginPage.php';

    expect($customFile)->toBeFile();

    unlink($customFile);
    rmdir($customDir);
    rmdir(base_path('tests/Custom'));
});