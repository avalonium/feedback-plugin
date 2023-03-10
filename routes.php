<?php

use \Avalonium\Feedback\Http\Controllers\Api;

Route::get('/api/v1/update-amo-token', [Api::class, 'updateAmoToken'])->name('api.amo.token.update');
