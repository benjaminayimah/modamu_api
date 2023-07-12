<!doctype html>
<html lang="eng">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
<style>
.btn:hover {
    background-color: #0566de !important
}
</style>
<table style="width: 100%; background-color: #EFF3FF">
    <tbody>
        <tr>
            <td style="padding: 80px 0;">
                <table style="width: 60%; max-width: 550px; min-width: 420px; margin: 0 auto">
                    <tbody>
                        <tr>
                            <td style="font-family:'Neue Montreal',sans-serif; font-size: 17px; font-weight: 400; line-height: 1.5">
                                <div class="body-card" style="background-color: #fff; padding: 30px; border-radius: 16px">
                                    <p>
                                        <a href="https://www.modamuvillage.com" target="_blank">
                                            <img aria-hidden="true" src="https://modamu-api.rancroftdev.com/storage/modamu-email-logo.png" height="40" alt="modamu">
                                        </a>
                                    </p>
                                    <div style="padding: 12px 0">
                                        <span style="color: #0173FF; padding-bottom: 6px; font-weight: 600; font-size: 25px; display:inline-block">
                                            Your {{ $account_type ?? '' }} is ready!
                                        </span>
                                    </div>
                                    <div style="color: rgb(34, 34, 34);">
                                        <p>
                                            <strong>Hello {{ $name ?? '' }},</strong>
                                        </p>
                                        <p>
                                            Your modamu {{ $account_type ?? '' }} is successfully created. Please find your login details below.
                                        </p>
                                        <p>
                                            <div style="font-size:16px">
                                                <span>Email:</span>
                                                <span>{{ $email }}</span>
                                            </div>
                                            <div style="font-size:16px">
                                                <span>Password:</span>
                                                <span>{{ $password }}</span>
                                            </div>
                                        </p>
                                        <p>
                                            <a href="{{ $url ?? '' }}" target="_blank" class="btn" style="background-color: #0173FF; border-radius: 18px; text-align: center; color:#fff; display:block; padding: 14px 24px; text-decoration: none; margin: 32px 24px">Login to account</a>
                                        </p>
                                        <p style="margin-top: 32px">
                                            Best regards!<br/>
                                            Modamu Team.
                                        </p>
                                    </div>
                                </div>
                                <div style="padding: 20px 40px 0 40px;text-align: center">
                                    <div style="font-size: 12px; color: #7A7D84">
                                        <span>You received this email because you have created an account with Modamu.</span>
                                        <span> For more enquiries, contact us at <a style="color: #212121;" href="mailTo:info@modamuvillage.com">info@modamuvillage.com</a></span>
                                        <span> or visit our website <a style="color: #212121;" href="https://www.modamuvillage.com" target="_blank">www.modamuvillage.com</a> for more information.</span>
                                        <br>
                                        <p>
                                            <div>© 2023 Modamu. All Rights Reserved.</div>
                                        </p>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </td>
        </tr>
    </tbody>
</table>
</body>
</html>
