<!DOCTYPE html>
<html lang="id">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Voucher Hotel - {{ $hotel->booking_code ?: $hotel->hotel_name }}</title>
    <style>
        @page {
            margin: 15px;
            size: a4 portrait;
        }
        body {
            font-family: 'DejaVu Sans', 'Helvetica Neue', Helvetica, Arial, sans-serif;
            background-color: #020617;
            color: #f8fafc;
            margin: 0;
            padding: 10px;
            font-size: 11px;
            line-height: 1.4;
        }
        .container {
            max-width: 650px;
            margin: 0 auto;
            background-color: #0f172a;
            border-radius: 16px;
            overflow: hidden;
            border: 1px solid #1e293b;
        }
        .header-table {
            width: 100%;
            background-color: #d97706;
            border-collapse: collapse;
        }
        .header-cell {
            padding: 22px 26px;
            color: #ffffff;
        }
        .brand-subtitle {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #fef3c7;
            font-weight: bold;
        }
        .hotel-name {
            font-size: 20px;
            font-weight: bold;
            color: #ffffff;
            margin-top: 4px;
        }
        .booking-code {
            font-size: 14px;
            font-weight: bold;
            font-family: monospace;
            color: #fef3c7;
            margin-top: 2px;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 9999px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-lunas {
            background-color: #064e3b;
            color: #34d399;
            border: 1px solid #059669;
        }
        .badge-belum-bayar {
            background-color: #78350f;
            color: #fcd34d;
            border: 1px solid #d97706;
        }
        .badge-dibatalkan {
            background-color: #881337;
            color: #fda4af;
            border: 1px solid #e11d48;
        }
        .dates-table {
            width: 100%;
            margin-top: 15px;
            padding-top: 12px;
            border-top: 1px solid rgba(255, 255, 255, 0.25);
            border-collapse: collapse;
        }
        .date-label {
            font-size: 9px;
            text-transform: uppercase;
            color: #fef3c7;
            display: block;
        }
        .date-value {
            font-size: 14px;
            font-weight: bold;
            color: #ffffff;
            margin-top: 2px;
            display: block;
        }
        .body-content {
            padding: 20px 24px;
        }
        .section-box {
            background-color: #1e293b;
            border-radius: 12px;
            padding: 12px 16px;
            margin-bottom: 12px;
            border: 1px solid #334155;
        }
        .section-title {
            font-size: 10px;
            font-weight: bold;
            color: #fbbf24;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }
        .guest-item {
            font-size: 12px;
            font-weight: bold;
            color: #f8fafc;
            padding: 2px 0;
        }
        .grid-table {
            width: 100%;
            border-collapse: collapse;
        }
        .grid-cell {
            width: 50%;
            padding: 4px 6px;
            vertical-align: top;
        }
        .info-label {
            font-size: 9px;
            color: #94a3b8;
            text-transform: uppercase;
            display: block;
        }
        .info-value {
            font-size: 12px;
            font-weight: bold;
            color: #f8fafc;
            margin-top: 2px;
            display: block;
        }
        .info-value-emerald {
            color: #34d399;
            font-family: monospace;
            font-size: 14px;
        }
        .info-value-indigo {
            color: #a5b4fc;
        }
        .timeline-item {
            padding-left: 12px;
            border-left: 2px solid #fbbf24;
            margin-bottom: 8px;
        }
        .timeline-status {
            font-size: 11px;
            font-weight: bold;
            color: #fbbf24;
        }
        .timeline-notes {
            font-size: 10px;
            color: #cbd5e1;
            margin-top: 1px;
        }
        .timeline-meta {
            font-size: 9px;
            color: #64748b;
            font-family: monospace;
            margin-top: 1px;
        }
        .barcode-section {
            background-color: #020617;
            padding: 14px;
            text-align: center;
            border-top: 1px solid #1e293b;
        }
        .barcode-lines {
            font-family: monospace;
            font-size: 20px;
            letter-spacing: 5px;
            color: #475569;
            font-weight: bold;
        }
        .footer-text {
            font-size: 9px;
            color: #64748b;
            font-family: monospace;
            margin-top: 4px;
        }
    </style>
</head>
<body>

    <div class="container">
        <!-- Header Banner -->
        <table class="header-table">
            <tr>
                <td class="header-cell">
                    @php
                        $badgeClass = match($hotel->status) {
                            'Lunas' => 'badge-lunas',
                            'Dibatalkan' => 'badge-dibatalkan',
                            default => 'badge-belum-bayar',
                        };
                    @endphp
                    <div style="float: right;">
                        <span class="status-badge {{ $badgeClass }}">{{ $hotel->status }}</span>
                    </div>
                    <span class="brand-subtitle">HOTEL RESERVATION VOUCHER</span>
                    <div class="hotel-name">{{ $hotel->hotel_name }}</div>
                    <div class="booking-code">
                        KODE BOOKING: {{ $hotel->booking_code ?: '-' }}
                        <span style="font-size: 11px; margin-left: 8px; color: #fef3c7;">(INV: {{ $hotel->invoice_code }})</span>
                    </div>

                    <!-- Dates Table -->
                    <table class="dates-table">
                        <tr>
                            <td style="width: 38%; vertical-align: middle;">
                                <span class="date-label">TANGGAL CHECK-IN</span>
                                <span class="date-value">{{ $hotel->check_in_date ? $hotel->check_in_date->format('d M Y') : '-' }}</span>
                            </td>
                            <td style="width: 24%; text-align: center; vertical-align: middle; font-size: 13px; color: #fef3c7; font-weight: bold;">
                                {{ $hotel->night_count }} MALAM<br>
                                <span style="font-size: 10px; color: #fbbf24;">({{ $hotel->room_count }} KAMAR)</span>
                            </td>
                            <td style="width: 38%; text-align: right; vertical-align: middle;">
                                <span class="date-label">TANGGAL CHECK-OUT</span>
                                <span class="date-value">{{ $hotel->check_out_date ? $hotel->check_out_date->format('d M Y') : '-' }}</span>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <!-- Body Content -->
        <div class="body-content">
            <!-- Guests List -->
            <div class="section-box">
                <div class="section-title">DAFTAR TAMU MENGINAP ({{ $hotel->guest_count }} ORANG • {{ $hotel->room_count }} KAMAR)</div>
                @foreach($hotel->guests_list as $index => $guest)
                    <div class="guest-item">{{ $index + 1 }}. {{ strtoupper($guest) }}</div>
                @endforeach
            </div>

            <!-- Core Details Grid -->
            <div class="section-box">
                <table class="grid-table">
                    <tr>
                        <td class="grid-cell">
                            <span class="info-label">TANGGAL BOOKING</span>
                            <span class="info-value">{{ $hotel->booking_date ? $hotel->booking_date->format('d M Y') : '-' }}</span>
                        </td>
                        <td class="grid-cell">
                            <span class="info-label">BIAYA RESERVASI HOTEL</span>
                            <span class="info-value info-value-emerald">{{ $hotel->formatted_amount }}</span>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Booker & Payer Details Grid -->
            <div class="section-box">
                <table class="grid-table">
                    <tr>
                        <td class="grid-cell">
                            <span class="info-label">PEMESAN HOTEL</span>
                            <span class="info-value info-value-indigo">{{ $hotel->booked_by }}</span>
                        </td>
                        <td class="grid-cell">
                            <span class="info-label">PEMBAYARAN OLEH</span>
                            <span class="info-value info-value-emerald">{{ $hotel->paid_by }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td class="grid-cell" style="padding-top: 8px;">
                            <span class="info-label">TANGGAL BOOKING</span>
                            <span class="info-value">{{ $hotel->booking_date ? $hotel->booking_date->format('d M Y') : '-' }}</span>
                        </td>
                        <td class="grid-cell" style="padding-top: 8px;">
                            <span class="info-label">TANGGAL BAYAR</span>
                            <span class="info-value">{{ $hotel->payment_date ? $hotel->payment_date->format('d M Y') : '-' }}</span>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Notes if present -->
            @if($hotel->notes)
                <div class="section-box">
                    <div class="section-title">CATATAN / KETERANGAN</div>
                    <div style="color: #cbd5e1; font-style: italic;">{{ $hotel->notes }}</div>
                </div>
            @endif

            <!-- Status Activity Logs -->
            @if($hotel->statusLogs->count() > 0)
                <div class="section-box">
                    <div class="section-title">RIWAYAT STEPS STATUS HOTEL (ACTIVITY LOG)</div>
                    @foreach($hotel->statusLogs as $log)
                        <div class="timeline-item">
                            <div class="timeline-status">
                                {{ $log->to_status }} @if($log->from_status)<span style="color: #64748b; font-weight: normal;">(dari {{ $log->from_status }})</span>@endif
                            </div>
                            <div class="timeline-notes">{{ $log->notes }}</div>
                            <div class="timeline-meta">{{ $log->user_name }} ({{ ucfirst($log->user_role ?? 'user') }}) • {{ $log->created_at->format('d M Y, H:i') }}</div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Barcode & Verification Footer -->
        <div class="barcode-section">
            <div class="barcode-lines">||||| ||| ||||||| ||| ||||| ||||</div>
            <div class="footer-text">VERIFIED HISTORICAL HOTEL VOUCHER RECORD • {{ $hotel->booking_code ?: '-' }} • TICKETTRACE SYSTEM</div>
        </div>
    </div>

</body>
</html>
