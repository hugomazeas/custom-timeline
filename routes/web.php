<?php

use App\Http\Controllers\TimelineController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('timeline.index');
})->name('timeline.index');

// API routes for pure JS frontend
Route::prefix('api')->group(function () {
    Route::get('/timeline-groups', [TimelineController::class, 'getGroups']);
    Route::post('/timeline-groups', [TimelineController::class, 'createGroup']);
    Route::delete('/timeline-groups/{id}', [TimelineController::class, 'deleteGroup']);

    Route::post('/timeline-rows', [TimelineController::class, 'createRow']);
    Route::delete('/timeline-rows/{id}', [TimelineController::class, 'deleteRow']);

    Route::post('/timeline-events', [TimelineController::class, 'createEvent']);
    Route::delete('/timeline-events/{id}', [TimelineController::class, 'deleteEvent']);
});
