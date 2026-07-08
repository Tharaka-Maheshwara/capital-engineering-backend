<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreContactRequest;
use App\Mail\ContactFormMail;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function store(StoreContactRequest $request): JsonResponse
    {
        if ($request->filled('honey')) {
            return response()->json([
                'message' => 'Thanks for reaching out. We will get back to you soon.',
            ]);
        }

        $contact = [
            'name' => $request->string('name')->toString(),
            'email' => $request->string('email')->toString(),
            'phone' => $request->string('phone')->trim()->toString(),
            'subject' => $request->string('subject')->trim()->toString(),
            'message' => $request->string('message')->toString(),
        ];

        $recipientEmail = config('mail.from.address') ?: 'info.lankacapital@gmail.com';
        $recipientName = config('mail.from.name') ?: config('app.name', 'Capital Engineering');

        $mail = (new ContactFormMail($contact))
            ->replyTo($contact['email'], $contact['name']);

        Mail::to($recipientEmail, $recipientName)->send($mail);

        return response()->json([
            'message' => 'Thanks for reaching out. We will get back to you soon.',
        ]);
    }
}