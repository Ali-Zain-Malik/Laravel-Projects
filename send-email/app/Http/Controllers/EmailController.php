<?php

namespace App\Http\Controllers;

use App\Mail\Mailer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class EmailController extends Controller
{
    public function sendMail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "email"     =>  "required|email",
            "subject"   =>  "nullable",
            "content"   =>  "required",
        ]);

        if($validator->fails())
        {
            return back()->withErrors($validator);
        }

        try
        {
            Mail::to($request->email)->send(new Mailer($request->subject, $request->content));
            return view("success", ["message" => "Mail sent successfully"]);
        }
        catch (\Exception $e)
        {
            // return back()->withErrors($e->getMessage());
        }
    }
}
