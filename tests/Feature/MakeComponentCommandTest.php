<?php

declare(strict_types=1);

use Thelemon2020\PestPom\Config;

afterEach(function () {
    $dir = Config::componentsAbsolutePath();

    if (is_dir($dir)) {
        foreach (glob($dir.DIRECTORY_SEPARATOR.'*.php') ?: [] as $file) {
            unlink($file);
        }
        rmdir($dir);
    }
});

it('creates a component file in the components directory', function () {
    $this->artisan('pest:component', ['name' => 'Navbar'])->assertSuccessful();

    expect(Config::componentsAbsolutePath().DIRECTORY_SEPARATOR.'NavbarComponent.php')->toBeFile();
});

it('appends the Component suffix when it is missing', function () {
    $this->artisan('pest:component', ['name' => 'Navbar'])->assertSuccessful();

    expect(Config::componentsAbsolutePath().DIRECTORY_SEPARATOR.'NavbarComponent.php')->toBeFile();
});

it('does not double the Component suffix', function () {
    $this->artisan('pest:component', ['name' => 'NavbarComponent'])->assertSuccessful();

    expect(Config::componentsAbsolutePath().DIRECTORY_SEPARATOR.'NavbarComponent.php')->toBeFile();
    expect(Config::componentsAbsolutePath().DIRECTORY_SEPARATOR.'NavbarComponentComponent.php')->not->toBeFile();
});

it('fails when the file already exists', function () {
    $this->artisan('pest:component', ['name' => 'Navbar'])->assertSuccessful();
    $this->artisan('pest:component', ['name' => 'Navbar'])->assertFailed();
});

it('creates the components directory when it does not exist', function () {
    $dir = Config::componentsAbsolutePath();
    expect($dir)->not->toBeDirectory();

    $this->artisan('pest:component', ['name' => 'Navbar'])->assertSuccessful();

    expect($dir)->toBeDirectory();
});

it('generates a class that extends Component', function () {
    $this->artisan('pest:component', ['name' => 'Navbar'])->assertSuccessful();

    $content = file_get_contents(Config::componentsAbsolutePath().DIRECTORY_SEPARATOR.'NavbarComponent.php');

    expect($content)->toContain('extends Component');
});

it('generates a selector method stub', function () {
    $this->artisan('pest:component', ['name' => 'Navbar'])->assertSuccessful();

    $content = file_get_contents(Config::componentsAbsolutePath().DIRECTORY_SEPARATOR.'NavbarComponent.php');

    expect($content)->toContain('public static function selector(): string');
});

it('includes requested concerns in the generated class', function () {
    $this->artisan('pest:component', ['name' => 'Navbar', '--concerns' => 'forms,navigation'])->assertSuccessful();

    $content = file_get_contents(Config::componentsAbsolutePath().DIRECTORY_SEPARATOR.'NavbarComponent.php');

    expect($content)
        ->toContain('use InteractsWithForms')
        ->toContain('use InteractsWithNavigation')
        ->toContain('use Thelemon2020\\PestPom\\Concerns\\InteractsWithForms')
        ->toContain('use Thelemon2020\\PestPom\\Concerns\\InteractsWithNavigation');
});

it('warns about unknown concerns and skips them', function () {
    $this->artisan('pest:component', ['name' => 'Navbar', '--concerns' => 'navigation,unknown'])
        ->assertSuccessful()
        ->expectsOutputToContain('Unknown concern [unknown]');

    $content = file_get_contents(Config::componentsAbsolutePath().DIRECTORY_SEPARATOR.'NavbarComponent.php');

    expect($content)
        ->toContain('use InteractsWithNavigation')
        ->not->toContain('unknown');
});

it('derives the namespace from the project PSR-4 map', function () {
    file_put_contents(base_path('composer.json'), json_encode([
        'autoload-dev' => ['psr-4' => ['Tests\\' => 'tests/']],
    ]));

    $this->artisan('pest:component', ['name' => 'Navbar'])->assertSuccessful();

    $content = file_get_contents(Config::componentsAbsolutePath().DIRECTORY_SEPARATOR.'NavbarComponent.php');

    expect($content)->toContain('namespace Tests\\Browser\\Components;');
});

it('falls back to Tests\\Browser\\Components when no PSR-4 map matches', function () {
    file_put_contents(base_path('composer.json'), json_encode([]));

    $this->artisan('pest:component', ['name' => 'Navbar'])->assertSuccessful();

    $content = file_get_contents(Config::componentsAbsolutePath().DIRECTORY_SEPARATOR.'NavbarComponent.php');

    expect($content)->toContain('namespace Tests\\Browser\\Components;');
});
