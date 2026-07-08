<?php

namespace Tests\Feature;

use App\Mail\ContactFormMail;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ContactFormTest extends TestCase
{
    public function test_contact_form_sends_mail_to_company_email(): void
    {
        Mail::fake();

        $response = $this->postJson('/api/v1/contact', [
            'name' => 'Site Visitor',
            'email' => 'visitor@example.com',
            'phone' => '+94 77 123 4567',
            'subject' => 'Residential',
            'message' => 'Please contact me about a new project.',
            'honey' => '',
        ]);

        $response->assertOk()
            ->assertJsonPath('message', 'Thanks for reaching out. We will get back to you soon.');

        Mail::assertSent(ContactFormMail::class, function (ContactFormMail $mail) {
            return $mail->contact['email'] === 'visitor@example.com'
                && $mail->contact['name'] === 'Site Visitor';
        });
    }
}