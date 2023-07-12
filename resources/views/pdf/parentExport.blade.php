<!doctype html>
<html lang="eng">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
</head>
<body>
<table style="width:100%">
    <tbody>
        <tr>
            <td>
                <table style="font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;width:100%;margin:0 auto">
                    <tbody>
                        <tr>
                            <td style="font-size:15px;font-weight:400;line-height:1.5;font-family:'Helvetica Neue',Helvetica,Arial,sans-serif">
                                <p>
                                    <div style=" padding:8px 0">
                                    <div style="text-align:center; font-size:22px;color:#0173FF">
                                        <img aria-hidden="true" src="https://modamu-api.rancroftdev.com/storage/modamu-email-logo.png" height="32" alt="modamu">
                                    </div>
                                        <div style="text-align:center">{{ $user->address ?? '' }}</div>
                                        <div style="text-align:center"><strong>Email:</strong> {{ $user->email ?? '' }}</div>
                                        <div style="text-align:center"><strong>Phone:</strong> {{ $user->phone ?? '' }}</div>
                                    </div>
                                </p>
                                <p style="border-top: 2px solid #0173FF">
                                @if($filter)
                                    <div style="text-transform:capitalize"><label><strong>Filter: </strong></label><span style="color:#0173FF">{{ $filter ?? '' }}</span></div>
                                @endif
                                    <div><label><strong>Date: </strong></label><span>{{ $time ?? '' }}</span></div>
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <table style="width:100%;margin:0 auto;font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;border-collapse:collapse;line-height:2">
                                    <thead>
                                        <tr>
                                            <th align="left">#</th>
                                            <th align="left">Name</th>
                                            <th align="left">No. of kids</th>
                                            <th align="left">Role</th>
                                            <th align="left">Phone No.</th>
                                            <th align="left">Emergency No.</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($parents as $key => $parent)
                                            <tr>
                                                <td>{{ $key+1 }}</td>
                                                <td>{{ $parent->name ?? '' }}</td>
                                                <td>{{ $parent->kids ?? '' }}</td>
                                                <td>{{ $parent->relationship ?? '' }}</td>
                                                <td>{{ $parent->phone ?? '' }}</td>
                                                <td>{{ $parent->emergency_number ?? '' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 100px">
                                <div style="font-family:'Helvetica Neue',Helvetica,Arial,sans-serif; padding: 20px 40px 0 40px;text-align: center">
                                    <div style="font-size: 12px; color: #7A7D84">
                                        <div>© 2023 Modamu Village. All Rights Reserved.</div>
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
