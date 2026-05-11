<?php

declare(strict_types=1);

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/_test/login', static function (Request $request): RedirectResponse {
    abort_unless($request->hasValidSignature(), 403);

    $modelClass = $request->query('model');
    $userId = $request->query('user');

    /** @var \Illuminate\Contracts\Auth\Authenticatable $user */
    $user = $modelClass::findOrFail($userId);
    auth()->login($user);

    return redirect($request->query('redirect', '/'));
})->name('pest-pom.test-login');
