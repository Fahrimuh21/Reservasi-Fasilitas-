<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
| The catch-all route below ensures ALL paths are handled by the Vue SPA.
| Without this, navigating directly to /facilities or /officer would return
| a Laravel 404, because those routes only exist in the client-side router.
|
| The wildcard pattern '.*' matches any path including nested segments.
|--------------------------------------------------------------------------
*/

Route::get('/{any}', function () {
    return view('welcome');
})->where('any', '.*');
