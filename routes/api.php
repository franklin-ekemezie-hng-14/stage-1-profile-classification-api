<?php
declare(strict_types=1);

use App\Http\Controllers\ProfileController;

Route::resource('profiles', ProfileController::class)
    ->except('create', 'edit');
