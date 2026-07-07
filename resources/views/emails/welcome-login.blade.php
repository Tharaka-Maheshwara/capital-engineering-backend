<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Welcome to Capital Engineering</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 30px auto; background: #ffffff; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .header { text-align: center; border-bottom: 2px solid #e2e8f0; padding-bottom: 20px; }
        .content { padding: 20px 0; color: #333333; line-height: 1.6; }
        .footer { text-align: center; color: #777777; font-size: 12px; border-top: 1px solid #e2e8f0; padding-top: 20px; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Welcome to Capital Engineering</h2>
        </div>
        <div class="content">
            <p>Hi <strong>{{ $user->name }}</strong>,</p>
            <p>Thank you for logging in to Capital Engineering for the first time! We are thrilled to have you on board.</p>
            <p>You can now explore our services, manage your projects, and estimate your dynamic pricing seamlessly.</p>
            <p>If you have any questions, feel free to reply to this email.</p>
            <br>
            <p>Best Regards,<br>The Capital Engineering Team</p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} Capital Engineering. All rights reserved.
        </div>
    </div>
</body>
</html>