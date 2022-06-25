<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>{{ config('app.name') }} - Reset Password Notification</title>
	<style type="text/css">
		#otp{
			background-color: #0c35b2;
		    color: white;
		    padding-left: 10px;
		    padding-right: 10px;
		    border: 2px solid black;
		}
	</style>
</head>
<body>
	<h2>Hello {{ $details['name'] }},</h2>
	<p>You are receiving this email because we received a password reset request for your account.</p><br>
	<p>Here is the OTP : <span id="otp"><b> {{ $details['otp'] }} </b></span></p><br>
	<p>This password reset OTP will expire in 60 minutes.</p>
	<p>If you did not request a password reset, no further action is required.</p>
	<p>Regards,</p>
	<p>{{ config('app.name') }}</p>
</body>
</html>