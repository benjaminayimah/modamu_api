<!doctype html>
<html lang="eng">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
</head>
<body>
<table style="width: 100%">
    <tbody>
        <tr>
            <td>
                <table style="font-family:'Helvetica Neue',Helvetica,Arial,sans-serif; width: 100%; margin: 0 auto">
                    <tbody style="line-height: 1.5">
                        <tr>
                            <td style="font-size: 15px; font-weight: 400; font-family:'Helvetica Neue',Helvetica,Arial,sans-serif">
                                <p>
                                    <div style="background-color: #EFF3FF; padding: 30px 0">
                                    <div style=" text-align: center; font-size: 22px; color: #0173FF"><strong>Modamu Village</strong></div>
                                        <div style="text-align: center">{{ $user->address ?? '' }}</div>
                                        <div style="text-align: center"><strong>Email:</strong> {{ $user->email ?? '' }}</div>
                                        <div style="text-align: center"><strong>Phone:</strong> {{ $user->phone ?? '' }}</div>
                                    </div>
                                </p>
                                <p>
                                    <div><label><strong>Filter: </strong></label><span>{{ $sale->added_by ?? '' }}</span></div>
                                    <div><label><strong>Date: </strong></label><span>{{ $sale->created_at ?? '' }}</span></div>
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <table style="width: 100%; margin: 0 auto; font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;border-collapse: collapse;">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Name</th>
                                            <th>No. of kids</th>
                                            <th>Role</th>
                                            <th>Phone No.</th>
                                            <th>Emergency No.</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td>
                            <hr style="border-top: 2px solid #e9ebf0; border-bottom: none; border-right: none; border-left: none">
                                <table style="width: 50%; margin-left: auto;border-collapse: collapse;">
                                    <tbody>
                                        <thead>
                                            <tr>
                                                <th style="padding: 12px 0; text-align: left">Sub total:</th>
                                                <th style="text-align: right;padding: 12px 0">GH3830</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td style="text-align: left; padding: 8px 0;color:#7A7D84;">Discount:</td>
                                                <td style="text-align: right; padding: 8px 0;color:#7A7D84;">0.00</td>
                                            </tr>
                                            <tr>
                                                <td style="text-align: left; padding: 8px 0;color:#7A7D84;">VAT(2%):</td>
                                                <td style="text-align: right; padding: 8px 0;color:#7A7D84;">2.00</td>
                                            </tr>
                                        </tbody>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <table style="width: 50%; margin-left: auto;border-collapse: collapse;font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;">
                                    <thead>
                                        <th style="padding: 12px 0; text-align: left; font-size: 24px">Total Amount:</th>
                                        <th style="padding: 12px 0; text-align: right; font-size: 24px"><span style="font-weight: 400;margin-right: 6px">{{ $store->currency_code }}</span><span>{{ number_format(round($sale->total_paid, 2)) }}</span></th>
                                    </thead>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 100px">
                                <div style="font-family:'Helvetica Neue',Helvetica,Arial,sans-serif; padding: 20px 40px 0 40px;text-align: center">
                                    <div style="font-size: 12px; color: #7A7D84">
                                        <span>Powered by: Flexsale Inc.</span><br />
                                        <span>For more enquiries, contact us at <a style="color: #212121;" href="mailTo:info@flexsale.store">info@flexsale.store</a></span>
                                        <span> or visit our website <a style="color: #212121;" href="https://www.flexsale.store" target="_blank">www.flexsale.store</a> for more information.</span>
                                        <br>
                                        <div>© 2022 Flexsale Inc. All Rights Reserved.</div>
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
