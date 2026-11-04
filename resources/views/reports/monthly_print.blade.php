<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Bulanan Bank Mini Sekolah - SMK N 1 Bantul</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 12px; color: #1e293b; padding: 25px; line-height: 1.5; }
        .header { text-align: center; border-bottom: 2px solid #0f172a; padding-bottom: 10px; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 18px; text-transform: uppercase; }
        .header p { margin: 2px 0; font-size: 11px; color: #475569; }
        .meta-box { margin-bottom: 20px; font-weight: bold; }
        .summary-grid { display: flex; gap: 15px; margin-bottom: 20px; }
        .summary-card { flex: 1; border: 1px solid #cbd5e1; padding: 12px; border-radius: 8px; background: #f8fafc; }
        .summary-card span { font-size: 10px; color: #64748b; text-transform: uppercase; font-weight: bold; }
        .summary-card h3 { margin: 5px 0 0; font-size: 16px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 25px; }
        th, td { border: 1px solid #cbd5e1; padding: 8px 10px; text-align: left; }
        th { background: #f1f5f9; font-weight: bold; text-transform: uppercase; font-size: 10px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .signatures { display: flex; justify-content: space-between; margin-top: 40px; text-align: center; }
        .sig-col { width: 220px; }
        .sig-space { height: 60px; }
        .sig-name { font-weight: bold; border-top: 1px solid #000; padding-top: 5px; }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 20px; background: #e0e7ff; padding: 12px 18px; border-radius: 8px; display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <button onclick="handleGoBack()" style="padding: 8px 18px; background: #334155; color: white; border: none; border-radius: 6px; font-weight: bold; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; font-size: 12px;">
                ⬅️ Kembali ke Halaman Sebelumnya
            </button>
            <span style="font-size: 12px;"><strong>Petunjuk Cetak:</strong> Klik tombol Cetak untuk menyimpan sebagai PDF atau langsung mencetak.</span>
        </div>
        <button onclick="window.print()" style="padding: 8px 20px; background: #4f46e5; color: white; border: none; border-radius: 6px; font-weight: bold; cursor: pointer; font-size: 12px;">
            🖨️ Cetak / Simpan PDF
        </button>
    </div>

    <div class="header" style="display: flex; align-items: center; justify-content: center; gap: 20px; border-bottom: 2px solid #0f172a; padding-bottom: 12px; margin-bottom: 20px; text-align: center;">
        <img src="{{ asset('images/logo.png') }}" alt="Logo SMK N 1 Bantul" style="height: 75px; width: 75px; object-fit: contain;">
        <div>
            <h2 style="margin: 0; font-size: 13px; letter-spacing: 0.5px; color: #475569; font-weight: bold; text-transform: uppercase;">Pemerintah Daerah Istimewa Yogyakarta</h2>
            <h1 style="margin: 2px 0; font-size: 18px; text-transform: uppercase; font-weight: 900; letter-spacing: 0.5px;">SMK NEGERI 1 BANTUL</h1>
            <p style="margin: 2px 0; font-size: 11px; font-weight: bold; color: #1e293b;">BANK MINI SEKOLAH • PROGRAM KEAHLIAN REKAYASA PERANGKAT LUNAK</p>
            <p style="margin: 2px 0; font-size: 10px; color: #64748b;">Jl. Parangtritis Km 11, Sabdodadi, Bantul, D.I. Yogyakarta 55715</p>
            <p style="margin: 6px 0 0; font-size: 12px; font-weight: bold; text-decoration: underline;">LAPORAN REKAPITULASI KAS & TRANSAKSI BULANAN</p>
        </div>
    </div>

    <div class="meta-box">
        Periode Laporan: <span id="lblPeriod">Memuat...</span>
    </div>

    <div class="summary-grid">
        <div class="summary-card">
            <span>Total Setoran Masuk</span>
            <h3 id="lblDeposit" style="color: #059669;">Rp 0</h3>
        </div>
        <div class="summary-card">
            <span>Total Penarikan Keluar</span>
            <h3 id="lblWithdraw" style="color: #e11d48;">Rp 0</h3>
        </div>
        <div class="summary-card">
            <span>Arus Kas Bersih (Net)</span>
            <h3 id="lblNet" style="color: #4f46e5;">Rp 0</h3>
        </div>
        <div class="summary-card">
            <span>Total Transaksi</span>
            <h3 id="lblTxCount">0</h3>
        </div>
    </div>

    <h4>Rincian Mutasi Transaksi Harian:</h4>
    <table>
        <thead>
            <tr>
                <th class="text-center">Tanggal</th>
                <th class="text-right">Total Setoran</th>
                <th class="text-right">Total Penarikan</th>
                <th class="text-right">Net Kas Harian</th>
                <th class="text-center">Volume Trx</th>
            </tr>
        </thead>
        <tbody id="tbodyDaily">
            <tr><td colspan="5" class="text-center">Memuat rincian harian...</td></tr>
        </tbody>
    </table>

    <div class="signatures">
        <div class="sig-col">
            <p>Mengetahui,<br><strong>Supervisor Bank Mini</strong></p>
            <div class="sig-space"></div>
            <div class="sig-name">( Supervisor Loket )</div>
        </div>
        <div class="sig-col">
            <p>Dibuat Oleh,<br><strong>Administrator Sistem</strong></p>
            <div class="sig-space"></div>
            <div class="sig-name">( Administrator )</div>
        </div>
    </div>

    <script>
        function handleGoBack() {
            if (window.history.length > 1) {
                window.history.back();
            } else {
                window.location.href = '/admin/dashboard';
            }
        }

        const urlParams = new URLSearchParams(window.location.search);
        const month = urlParams.get('month') || (new Date().getMonth() + 1);
        const year = urlParams.get('year') || new Date().getFullYear();

        async function loadReport() {
            const token = localStorage.getItem('bank_token') || localStorage.getItem('auth_token');
            const res = await fetch(`/api/admin/reports/monthly?month=${month}&year=${year}`, {
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                }
            });
            const result = await res.json();
            if (result.success) {
                const d = result.data;
                document.getElementById('lblPeriod').innerText = d.period_name;
                document.getElementById('lblDeposit').innerText = 'Rp ' + d.total_deposit.toLocaleString('id-ID');
                document.getElementById('lblWithdraw').innerText = 'Rp ' + d.total_withdraw.toLocaleString('id-ID');
                document.getElementById('lblNet').innerText = 'Rp ' + d.net_cashflow.toLocaleString('id-ID');
                document.getElementById('lblTxCount').innerText = d.total_tx + ' Transaksi';

                const tbody = document.getElementById('tbodyDaily');
                if (d.daily_records.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="5" class="text-center">Tidak ada transaksi pada bulan ini.</td></tr>';
                } else {
                    tbody.innerHTML = d.daily_records.map(r => `
                        <tr>
                            <td class="text-center">${r.tx_date}</td>
                            <td class="text-right">Rp ${Number(r.total_deposit).toLocaleString('id-ID')}</td>
                            <td class="text-right">Rp ${Number(r.total_withdraw).toLocaleString('id-ID')}</td>
                            <td class="text-right font-bold">Rp ${(Number(r.total_deposit) - Number(r.total_withdraw)).toLocaleString('id-ID')}</td>
                            <td class="text-center">${r.total_count}</td>
                        </tr>
                    `).join('');
                }
            }
        }
        document.addEventListener('DOMContentLoaded', loadReport);
    </script>
</body>
</html>
