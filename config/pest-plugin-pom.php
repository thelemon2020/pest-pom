<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Pages Directory
    |--------------------------------------------------------------------------
    |
    | The path, relative to the project root, where Page Object classes live.
    | The pest:page generator will write new files here, and Page::open() will
    | enforce that every page class loaded at runtime comes from this directory.
    |
    */

    'path' => 'tests/Browser/Pages',

    /*
    |--------------------------------------------------------------------------
    | Login Route Middleware
    |--------------------------------------------------------------------------
    |
    | The middleware applied to the /_test/login route used by openAsUser().
    | Must include any middleware that starts the session (typically 'web').
    |
    */

    'login_middleware' => ['web'],

];