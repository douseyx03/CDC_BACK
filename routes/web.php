<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Route::view('/docs', 'docs.index');
Route::view('/docs/swagger', 'docs.swagger');
Route::view('/docs/redoc', 'docs.redoc');
Route::view('/docs/api-platform', 'docs.api-platform');
