<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AiContentController;

Route::redirect('/', '/ai-form');

Route::get('/ai-form', [AiContentController::class, 'showForm'])->name('ai.form');

// GET (if the user cold-reloads http://blog-ai.test/ai-generate") — shows the empty form
Route::get('/ai-generate', [AiContentController::class, 'showForm']);

// POST (normal user-input submission) — receives and processes the form submission
Route::post('/ai-generate', [AiContentController::class, 'generate'])->name('ai.generate');