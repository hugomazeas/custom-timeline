<?php

use App\Livewire\Timeline;
use Illuminate\Support\Facades\Route;

Route::get('/', Timeline::class)->name('timeline.index');
Route::get('/timeline', Timeline::class);
