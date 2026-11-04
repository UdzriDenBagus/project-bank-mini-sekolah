<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Closing Kas Harian Teller - SMK N 1 Bantul</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 12px; color: #1e293b; padding: 25px; line-height: 1.5; }
        .header { text-align: center; border-bottom: 2px solid #0f172a; padding-bottom: 10px; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 18px; text-transform: uppercase; }
        .header p { margin: 2px 0; font-size: 11px; color: #475569; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #cbd5e1; padding: 7px 10px; }
        th { background: #f1f5f9; text-transform: uppercase; font-size: 10px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .badge-del { background: #fee2e2; color: #991b1b; padding: 2px 6px; border-radius: 4px; font-size: 9px; font-weight: bold; }
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
            <span style="font-size: 12px;">Laporan Audit Closing Teller. Dokumen legalitas transaksi fisik vs sistem.</span>
        </div>
        <button onclick="window.print()" style="padding: 8px 20px; background: #059669; color: white; border: none; border-radius: 6px; font-weight: bold; cursor: pointer; font-size: 12px;">
            🖨️ Cetak Dokumen / Simpan PDF
        </button>
    </div>

    <div class="header" style="display: flex; align-items: center; justify-content: center; gap: 20px; border-bottom: 2px solid #0f172a; padding-bottom: 12px; margin-bottom: 20px; text-align: center;">
        <img src="{{ asset('images/logo.png') }}" alt="Logo SMK N 1 Bantul" style="height: 75px; width: 75px; object-fit: contain;">
        <div>
            <h2 style="margin: 0; font-size: 13px; letter-spacing: 0.5px; color: #475569; font-weight: bold; text-transform: uppercase;">Pemerintah Daerah Istimewa Yogyakarta</h2>
            <h1 style="margin: 2px 0; font-size: 18px; text-transform: uppercase; font-weight: 900; letter-spacing: 0.5px;">SMK NEGERI 1 BANTUL</h1>
            <p style="margin: 2px 0; font-size: 11px; font-weight: bold; color: #1e293b;">BANK MINI SEKOLAH • E-TELLER TRANSAKSI KASIR</p>
            <p style="margin: 2px 0; font-size: 10px; color: #64748b;">Jl. Parangtritis Km 11, Sabdodadi, Bantul, D.I. Yogyakarta 55715</p>
            <p style="margin: 6px 0 0; font-size: 12px; font-weight: bold; text-decoration: underline;">BERITA ACARA REKONSILIASI PENUTUPAN KAS (CLOSING TELLER)</p>
        </div>
    </div>

    <div style="margin-bottom: 15px;">
        <p style="margin: 2px 0;"><strong>Piket Teller:</strong> <span id="lblTeller">-</span></p>
        <p style="margin: 2px 0;"><strong>Tanggal Closing:</strong> <span id="lblDate">-</span></p>
        <p style="margin: 2px 0;"><strong>Status Validasi:</strong> <span id="lblStatus" style="text-transform: uppercase; font-weight: bold;">-</span></p>
    </div>

    <h4>Rincian Transaksi Teller:</h4>
    <table>
        <thead>
            <tr>
                <th class="text-center">Kode Transaksi</th>
                <th>Nama Nasabah / Penabung</th>
                <th class="text-center">No. Rekening</th>
                <th class="text-center">Jenis</th>
                <th class="text-right">Nominal (Rp)</th>
            </tr>
        </thead>
        <tbody id="txBody">
            <tr><td colspan="5" class="text-center">Memuat rincian transaksi...</td></tr>
        </tbody>
    </table>

    <div style="display: flex; justify-content: space-between; margin-top: 40px; text-align: center;">
        <div style="width: 200px;">
            <p>Piket Teller,<br><br><br><br><strong>( <span id="sigTeller">Teller</span> )</strong></p>
        </div>
        <div style="width: 200px;">
            <p>Disetujui Oleh Administrator,<br><br><br><br><strong>( <span id="sigSpv">Administrator</span> )</strong></p>
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

        const closingId = "{{ $closingId }}";

        async function loadAudit() {
            const token = localStorage.getItem('bank_token') || localStorage.getItem('auth_token');
            const res = await fetch(`/api/supervisor/closings/${closingId}/audit`, {
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                }
            });
            const result = await res.json();
            if (result.success) {
                const c = result.data.closing;
                const txs = result.data.transactions;

                document.getElementById('lblTeller').innerText = c.teller ? c.teller.name : '-';
                document.getElementById('sigTeller').innerText = c.teller ? c.teller.name : 'Teller';
                document.getElementById('lblDate').innerText = c.closing_date;
                document.getElementById('lblStatus').innerText = c.status;
                document.getElementById('sigSpv').innerText = c.supervisor ? c.supervisor.name : 'Administrator';

                const tbody = document.getElementById('txBody');
                if (txs.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="5" class="text-center">Tidak ada transaksi.</td></tr>';
                } else {
                    tbody.innerHTML = txs.map(t => {
                        // KUNCI LAPORAN AMAN:
                        // Jika akun penabung sudah dihapus, saver->deleted_at ada, atau ambil snapshot
                        const isDeleted = (t.saver && t.saver.deleted_at);
                        const name = t.saver ? t.saver.name : (t.saver_name_snapshot || 'Eks-Penabung');
                        const accNo = t.saver ? t.saver.account_number : (t.account_number_snapshot || '-');

                        return `
                            <tr>
                                <td class="text-center font-mono">${t.transaction_code}</td>
                                <td>
                                    <strong>${name}</strong>
                                    ${isDeleted ? '<span class="badge-del">Akun Nonaktif / Dihapus</span>' : ''}
                                </td>
                                <td class="text-center font-mono">${accNo}</td>
                                <td class="text-center uppercase font-bold">${t.transaction_type}</td>
                                <td class="text-right font-bold">Rp ${Number(t.amount).toLocaleString('id-ID')}</td>
                            </tr>
                        `;
                    }).join('');
                }
            }
        }
        document.addEventListener('DOMContentLoaded', loadAudit);
    </script>
</body>
</html>
