<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>New Contact Message</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 0; }
        .container { max-width: 640px; margin: 30px auto; background: #ffffff; padding: 24px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.08); }
        .header { border-bottom: 2px solid #e2e8f0; padding-bottom: 16px; margin-bottom: 20px; }
        .label { font-size: 12px; text-transform: uppercase; letter-spacing: 0.08em; color: #64748b; margin-bottom: 4px; }
        .value { font-size: 16px; color: #0f172a; margin-bottom: 14px; }
        .message { white-space: pre-wrap; line-height: 1.7; color: #0f172a; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px; }
        .footer { margin-top: 24px; padding-top: 16px; border-top: 1px solid #e2e8f0; color: #64748b; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2 style="margin: 0; color: #0f172a;">New Contact Form Message</h2>
        </div>

        <div class="label">Name</div>
        <div class="value">{{ $contact['name'] }}</div>

        <div class="label">Email</div>
        <div class="value">{{ $contact['email'] }}</div>

        @if(!empty($contact['phone']))
            <div class="label">Phone</div>
            <div class="value">{{ $contact['phone'] }}</div>
        @endif

        @if(!empty($contact['subject']))
            <div class="label">Project Type / Subject</div>
            <div class="value">{{ $contact['subject'] }}</div>
        @endif

        <div class="label">Message</div>
        <div class="message">{!! nl2br(e($contact['message'])) !!}</div>

        <div class="footer">
            This message was submitted from the Capital Engineering website contact form.
        </div>
    </div>
</body>
</html>