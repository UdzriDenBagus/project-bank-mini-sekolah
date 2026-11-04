<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pemberitahuan Keamanan: Reset {{ $typeLabel }} Berhasil</title>
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
            background: linear-gradient(135deg, #064e3b 0%, #065f46 50%, #047857 100%);
            padding: 35px 30px;
            text-align: center;
            color: #ffffff;
        }
        .brand-badge {
            display: inline-block;
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.3);
            padding: 6px 14px;
            border-radius: 50px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: #a7f3d0;
            margin-bottom: 12px;
        }
        .header h1 {
            margin: 0;
            font-size: 22px;
            font-weight: 900;
            letter-spacing: -0.5px;
        }
        .content {
            padding: 35px 30px;
        }
        .success-box {
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            border-radius: 16px;
            padding: 20px;
            text-align: center;
            margin: 20px 0;
        }
        .success-title {
            color: #065f46;
            font-weight: 800;
            font-size: 16px;
            margin-bottom: 6px;
        }
        .success-desc {
            color: #047857;
            font-size: 13px;
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
            background-color: #fff1f2;
            border-left: 4px solid #f43f5e;
            padding: 14px 16px;
            border-radius: 0 10px 10px 0;
            font-size: 12px;
            line-height: 1.5;
            color: #9f1239;
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
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="brand-badge">Keamanan Akun Terjaga</div>
            <h1>Reset {{ $typeLabel }} Berhasil</h1>
        </div>

        <!-- Body -->
        <div class="content">
            <div style="font-size: 16px; font-weight: 700; color: #0f172a; margin-bottom: 12px;">Halo, {{ $targetName }}!</div>
            
            <div class="success-box">
                <div class="success-title">✓ Pengaturan Ulang Berhasil Dilakukan</div>
                <div class="success-desc">{{ $typeLabel }} akun Anda telah berhasil diperbarui dan kini telah aktif.</div>
            </div>

            <!-- Detail Perubahan -->
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
                    <td class="info-label">Waktu Perubahan</td>
                    <td class="info-value">{{ date('d M Y, H:i') }} WIB</td>
                </tr>
            </table>

            <!-- Security Alert -->
            <div class="warning-box">
                <strong>Peringatan Keamanan:</strong> Jika Anda tidak merasa melakukan perubahan {{ strtolower($typeLabel) }} ini, segera hubungi Administrator Bank Mini Sekolah atau Petugas Teller untuk mengamankan akun Anda.
            </div>

            <p style="font-size: 13px; color: #64748b; margin-top: 25px;">
                Salam hangat,<br>
                <strong>Tim Administrasi & E-Teller Bank Mini Sekolah</strong>
            </p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p><strong>E-Teller Bank Mini Sekolah</strong> • Sistem Informasi Keuangan & Buku Tabungan Digital</p>
            <p>Email ini dikirimkan secara otomatis sebagai notifikasi keamanan akun Anda.</p>
        </div>
    </div>
</body>
</html>
