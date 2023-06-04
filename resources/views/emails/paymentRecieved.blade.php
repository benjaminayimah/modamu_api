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
                                        <span style="color: #0173FF; font-weight: 600; font-size: 25px; display:inline-block">
                                            Payment received!
                                        </span>
                                    </div>
                                    <div style="color: rgb(34, 34, 34);">
                                        <p>
                                            <strong>Dear {{ $name }},</strong>
                                        </p>
                                        <p>
                                            We have recieved your booking. Please find the details below.
                                        </p>
                                        <p style="border-bottom: 1px solid #eee">
                                            <span style="color: #888; font-size: 13px; text-transform:uppercase">Booking details</span>
                                        </p>
                                        <table style="width: 100%">
                                            <tbody>
                                            <tr style="font-size: 16px;">
                                                    <td style="color: #000">Reciept No: {{ $booking_no }}</td>
                                                </tr>
                                                <tr style="font-size: 16px">
                                                    <td style="color: #666">No. of registered kids</td>
                                                    <td style="color: #000" align="right">{{ $number_of_kids }}</td>
                                                </tr>
                                                <tr style="font-size: 16px;">
                                                    <td style="color: #666">Amount per child</td>
                                                    <td style="color: #000" align="right">${{ $amount_per_child }}</td>
                                                </tr>
                                                <tr style="font-size: 24px">
                                                    <td><strong>Total amount:</strong></td>
                                                    <td style="color: #000" align="right"><strong>${{ $total_amount }}</strong></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                        <p style="border-bottom: 1px solid #eee">
                                            <span style="color: #888; font-size: 13px; text-transform:uppercase">Event details</span>
                                        </p>
                                        <table style="width: 100%">
                                            <tbody>
                                                <tr style="font-size: 16px">
                                                    <td style="color: #666">Event name</td>
                                                    <td style="color: #000" align="right">{{ $event_name }}</td>
                                                </tr>
                                                <tr style="font-size: 16px">
                                                    <td style="color: #666">Village name</td>
                                                    <td style="color: #000" align="right">{{ $village_name }}</td>
                                                </tr>
                                                <tr style="font-size: 16px">
                                                    <td style="color: #666">Address</td>
                                                    <td style="color: #000" align="right">{{ $address }}</td>
                                                </tr>
                                                <tr style="font-size: 16px;">
                                                    <td style="color: #666">Date</td>
                                                    <td style="color: #000" align="right">{{ $date }}</td>
                                                </tr>
                                                <tr style="font-size: 16px;">
                                                    <td style="color: #666">Start time</td>
                                                    <td style="color: #000" align="right">{{ $start_time }}</td>
                                                </tr>
                                                <tr style="font-size: 16px;">
                                                    <td style="color: #666">End time</td>
                                                    <td style="color: #000" align="right">{{ $end_time }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                        <p>
                                            <span>Keep track of the status of your booking by following this link </span><a href="{{ $url }}">{{ $url }}</a>
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
