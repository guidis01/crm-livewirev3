<?php

use App\Livewire\Welcome;
use Illuminate\Support\Facades\Route;

Route::get('/', Welcome::class);

Route::get('/home', Welcome::class)->name('home');
