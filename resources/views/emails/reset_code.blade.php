<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kode Verifikasi Reset {{ $typeLabel }}</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f1f5f9;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            color: #334155;
            -webkit-text-size-adjust: 100%;
        }
        .container {
            max-width: 580px;
            margin: 30px auto;
            background: #ffffff;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.08), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
            border: 1px solid #e2e8f0;
        }
        .header {
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #312e81 100%);
            padding: 35px 30px;
            text-align: center;
            color: #ffffff;
        }
        .brand-badge {
            display: inline-block;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.25);
            padding: 6px 14px;
            border-radius: 50px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: #fbbf24;
            margin-bottom: 12px;
        }
        .header h1 {
            margin: 0;
            font-size: 22px;
            font-weight: 900;
            letter-spacing: -0.5px;
        }
        .header p {
            margin: 6px 0 0;
            font-size: 13px;
            color: #cbd5e1;
        }
        .content {
            padding: 35px 30px;
        }
        .greeting {
            font-size: 16px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 12px;
        }
        .text {
            font-size: 14px;
            line-height: 1.6;
            color: #475569;
            margin-bottom: 20px;
        }
        .otp-card {
            background: #f8fafc;
            border: 2px dashed #6366f1;
            border-radius: 16px;
            padding: 24px;
            text-align: center;
            margin: 25px 0;
        }
        .otp-label {
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #4f46e5;
            margin-bottom: 8px;
        }
        .otp-code {
            font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, Courier, monospace;
            font-size: 36px;
            font-weight: 900;
            letter-spacing: 10px;
            color: #1e1b4b;
            padding: 6px 0;
            display: inline-block;
        }
        .otp-expiry {
            font-size: 12px;
            color: #64748b;
            margin-top: 8px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 13px;
            background: #f8fafc;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }
        .info-table td {
            padding: 10px 14px;
            border-bottom: 1px solid #e2e8f0;
        }
        .info-table tr:last-child td {
            border-bottom: none;
        }
        .info-label {
            color: #64748b;
            font-weight: 600;
            width: 40%;
        }
        .info-value {
            color: #0f172a;
            font-weight: 700;
        }
        .warning-box {
            background-color: #fffbeb;
            border-left: 4px solid #f59e0b;
            padding: 14px 16px;
            border-radius: 0 10px 10px 0;
            font-size: 12px;
            line-height: 1.5;
            color: #92400e;
            margin: 20px 0;
        }
        .footer {
            background: #f8fafc;
            padding: 24px 30px;
            text-align: center;
            font-size: 12px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
        }
        .footer p {
            margin: 4px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="brand-badge">Bank Mini Sekolah</div>
            <h1>Verifikasi Keamanan Akun</h1>
            <p>Permintaan Reset {{ $typeLabel }}</p>
        </div>

        <!-- Body -->
        <div class="content">
            <div class="greeting">Halo, {{ $targetName }}!</div>
            <p class="text">
                Kami menerima permintaan untuk melakukan pengaturan ulang (reset) <strong>{{ $typeLabel }}</strong> pada akun Bank Mini Sekolah Anda. Gunakan kode OTP 6-digit berikut untuk menyelesaikan proses verifikasi:
            </p>

            <!-- OTP Box -->
            <div class="otp-card">
                <div class="otp-label">Kode Verifikasi OTP Anda</div>
                <div class="otp-code">{{ $resetCode }}</div>
                <div class="otp-expiry">⏱️ Berlaku selama <strong>{{ $expiresIn ?? '30 Menit' }}</strong></div>
            </div>

            <!-- Detail Permintaan -->
            <table class="info-table">
                <tr>
                    <td class="info-label">Peran Akun</td>
                    <td class="info-value">{{ strtoupper($role) }}</td>
                </tr>
                <tr>
                    <td class="info-label">Identitas Pengguna</td>
                    <td class="info-value">{{ $identifier }}</td>
                </tr>
                <tr>
                    <td class="info-label">Tipe Reset</td>
                    <td class="info-value">{{ $typeLabel }}</td>
                </tr>
                <tr>
                    <td class="info-label">Waktu Permintaan</td>
                    <td class="info-value">{{ date('d M Y, H:i') }} WIB</td>
                </tr>
            </table>

            <!-- Security Alert -->
            <div class="warning-box">
                <strong>Penting & Rahasia:</strong> Jangan pernah membagikan kode verifikasi ini kepada siapapun, termasuk pihak sekolah atau petugas bank. Abaikan email ini jika Anda tidak merasa mengajukan permintaan reset.
            </div>

            <p class="text" style="font-size: 13px; color: #64748b; margin-top: 25px;">
                Salam hangat,<br>
                <strong>Tim Administrasi & E-Teller Bank Mini Sekolah</strong>
            </p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p><strong>E-Teller Bank Mini Sekolah</strong> • Sistem Informasi Keuangan & Buku Tabungan Digital</p>
            <p>Email ini dikirimkan secara otomatis dari sistem perbankan sekolah. Mohon tidak membalas email ini.</p>
        </div>
    </div>
</body>
</html>
