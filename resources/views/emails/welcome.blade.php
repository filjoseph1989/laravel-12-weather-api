<!-- resources/views/emails/welcome.blade.php -->
<!DOCTYPE html>
<html>

<head>
    <title>Welcome Aboard! You're Now Part of {{ config('app.name') }}</title>
</head>

<body>
    <h1>Welcome, {{ $userName }}!</h1>
    <p>Thank you for registering with us. Your account has been successfully created.</p>
    <p>Here’s your API token for authentication:</p>
    <pre>{{ $token }}</pre>
    <p>Feel free to use this token to access our API. Keep it secure!</p>
    <p>For any assistance, please contact our support team.</p>
    <p>Best regards,<br>The {{ config('app.name') }} Team</p>
</body>

</html>