<?php

use App\Http\Controllers\EmailController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('form');
});

Route::post("/send-email", [EmailController::class, "sendMail"])->name("send-email");
