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
                                        <span style="padding-bottom: 6px; font-weight: 600; font-size: 25px; display:inline-block">
                                            New message from Modamu
                                        </span>
                                    </div>
                                    <table style="width:100%">
                                        <tbody>
                                            <tr style="color: rgb(34, 34, 34);">
                                                <td>
                                                    <span style="font-size:20px">{{ $sender }} wrote,</span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 20px;border: 1px solid #eee;">{{ $chat }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <div style="color: rgb(34, 34, 34);">
                                        <p>
                                            <a href="{{ $url }}" target="_blank" class="btn" style="background-color: #0173FF; border-radius: 18px; text-align: center; color:#fff; display:block; padding: 14px 24px; text-decoration: none; margin: 32px 24px">View message details</a>
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
                                        <span style="opacity: 0">{{ $hideme }} </span>
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
