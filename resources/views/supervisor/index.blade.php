@extends('layouts.app')
@section('title', 'Panel Supervisor - Bank Mini Sekolah')

@section('content')
<div class="space-y-6">

    <!-- Header Panel Supervisor (Bebas Icon) -->
    <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <span class="text-xs font-black uppercase tracking-widest text-amber-600 bg-amber-50 px-3 py-1 rounded-full">Kendali Mutu & Otorisasi Brankas</span>
            <h2 class="text-2xl font-black text-slate-800 tracking-tight mt-1 flex items-center">
                Dashboard Supervisor Loket
            </h2>
            <p class="text-xs text-slate-500 mt-0.5">Validasi Fisik Kas Teller, Otorisasi Pemindahan ke Brankas Utama (102), Cetak Laporan Audit, & Dispute Resolution</p>
        </div>

        <button onclick="loadSupervisorClosings()" class="px-4 py-2.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-xl text-xs font-bold transition flex items-center self-start">
            Segarkan Laporan
        </button>
    </div>

    <!-- Tabel Daftar Closing Kas Harian Teller -->
    <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6 space-y-4">
        <h3 class="font-black text-slate-800 text-lg flex items-center">
            Laporan Closing Kas Loket Teller
        </h3>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b bg-slate-50 text-slate-600 text-xs uppercase font-bold">
                        <th class="py-3 px-4">Tanggal</th>
                        <th class="py-3 px-4">Piket Teller</th>
                        <th class="py-3 px-4 text-right">Saldo Sistem</th>
                        <th class="py-3 px-4 text-right">Uang Fisik Laci</th>
                        <th class="py-3 px-4 text-right">Selisih Kas</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-right">Aksi & Otorisasi</th>
                    </tr>
                </thead>
                <tbody id="supervisorClosingsBody">
                    <tr><td colspan="7" class="text-center py-6 text-slate-400 text-xs">Memuat laporan closing...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- ======================================================== -->
<!-- MODAL AUDIT DETAIL MUTASI TELLER (BEBAS ICON) -->
<!-- ======================================================== -->
<div id="modalAuditDetail" class="fixed inset-0 bg-black/60 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-2xl w-full p-6 shadow-2xl space-y-4 max-h-[85vh] flex flex-col">
        <div class="flex items-center justify-between border-b pb-3">
            <div>
                <h3 class="font-black text-slate-800 text-lg flex items-center">
                    Audit Transaksi Teller
                </h3>
                <p id="auditSubTitle" class="text-xs text-slate-500 mt-0.5">Detail transaksi harian</p>
            </div>
            <button onclick="closeModal('modalAuditDetail')" class="text-slate-400 hover:text-slate-700 text-lg font-bold">✕</button>
        </div>

        <div class="overflow-y-auto flex-1">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b bg-slate-50 text-slate-600 uppercase font-bold">
                        <th class="py-2.5 px-3">Kode TRX</th>
                        <th class="py-2.5 px-3">Nasabah / Penabung</th>
                        <th class="py-2.5 px-3 text-center">Jenis</th>
                        <th class="py-2.5 px-3 text-right">Nominal</th>
                    </tr>
                </thead>
                <tbody id="auditTableBody">
                    <tr><td colspan="4" class="text-center py-6 text-slate-400 text-xs">Memuat rincian transaksi...</td></tr>
                </tbody>
            </table>
        </div>

        <div class="border-t pt-3 flex justify-between items-center">
            <a id="btnModalPrintAudit" href="#" target="_blank" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs flex items-center shadow transition">
                Cetak Laporan Audit PDF
            </a>
            <button type="button" onclick="closeModal('modalAuditDetail')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs">Tutup Audit</button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function closeModal(id) {
        document.getElementById(id).classList.add('hidden');
    }

    // ================= LOAD CLOSINGS SUPERVISOR =================
    async function loadSupervisorClosings() {
        const res = await fetchAPI('/supervisor/closings');
        const tbody = document.getElementById('supervisorClosingsBody');
        if (res && res.data && res.data.success) {
            const list = res.data.data.data || res.data.data;
            if (list.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center py-6 text-slate-400 text-xs">Belum ada laporan closing yang diajukan oleh teller.</td></tr>';
                return;
            }

            tbody.innerHTML = list.map(c => {
                const diff = Number(c.difference);
                const isDispute = (c.status === 'dispute' || diff !== 0);
                const isApproved = (c.status === 'approved');

                return `
                    <tr class="border-b hover:bg-slate-50 transition">
                        <td class="py-3 px-4 text-xs font-bold text-slate-700">${c.closing_date}</td>
                        <td class="py-3 px-4 font-semibold text-slate-800">${c.teller ? c.teller.name : '-'}</td>
                        <td class="py-3 px-4 text-right font-bold text-slate-700">Rp ${Number(c.system_balance).toLocaleString('id-ID')}</td>
                        <td class="py-3 px-4 text-right font-bold text-indigo-700">Rp ${Number(c.physical_balance).toLocaleString('id-ID')}</td>
                        <td class="py-3 px-4 text-right font-black ${diff === 0 ? 'text-emerald-600' : 'text-rose-600'}">
                            ${diff === 0 ? 'Klop (Rp 0)' : (diff > 0 ? '+Rp ' : '-Rp ') + Math.abs(diff).toLocaleString('id-ID')}
                        </td>
                        <td class="py-3 px-4 text-center">
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase ${
                                isApproved ? 'bg-emerald-100 text-emerald-800' :
                                isDispute ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800'
                            }">
                                ${c.status}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-right space-x-1 whitespace-nowrap">
                            <button onclick="auditDetail(${c.id})" class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-bold transition inline-flex items-center">
                                Audit
                            </button>
                            <a href="/reports/closing/${c.id}/print" target="_blank" class="px-2.5 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-lg text-xs font-bold transition inline-flex items-center">
                                Cetak PDF
                            </a>
                            ${!isApproved ? `
                                <button onclick="approveClosing(${c.id}, ${diff})" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition shadow inline-flex items-center">
                                    Approve & Vault
                                </button>
                            ` : '<span class="text-xs text-emerald-600 font-bold ml-2 inline-flex items-center">Approved</span>'}
                        </td>
                    </tr>
                `;
            }).join('');
        }
    }

    // ================= AUDIT DETAIL TRANSAKSI TELLER =================
    async function auditDetail(closingId) {
        document.getElementById('btnModalPrintAudit').href = `/reports/closing/${closingId}/print`;
        const res = await fetchAPI(`/supervisor/closings/${closingId}/audit`);
        if (res && res.data && res.data.success) {
            const closing = res.data.data.closing;
            const transactions = res.data.data.transactions;

            document.getElementById('auditSubTitle').innerText = `Teller: ${closing.teller ? closing.teller.name : '-'} | Tanggal: ${closing.closing_date}`;
            const tbody = document.getElementById('auditTableBody');

            if (transactions.length === 0) {
                tbody.innerHTML = '<tr><td colspan="4" class="text-center py-6 text-slate-400 text-xs">Tidak ada mutasi transaksi pada tanggal ini.</td></tr>';
            } else {
                tbody.innerHTML = transactions.map(t => {
                    const isDeleted = (t.saver && t.saver.deleted_at);
                    const name = t.saver ? t.saver.name : (t.saver_name_snapshot || (t.customer ? t.customer.name : 'Eks-Penabung'));
                    const accNo = t.saver ? t.saver.account_number : (t.account_number_snapshot || (t.customer ? t.customer.account_number : '-'));

                    return `
                        <tr class="border-b hover:bg-slate-50">
                            <td class="py-2.5 px-3 font-mono font-bold text-slate-700">${t.transaction_code}</td>
                            <td class="py-2.5 px-3 font-semibold">
                                ${name} (${accNo})
                                ${isDeleted ? '<span class="ml-1 text-[9px] px-1.5 py-0.5 bg-rose-100 text-rose-800 rounded font-bold">Nonaktif/Dihapus</span>' : ''}
                            </td>
                            <td class="py-2.5 px-3 text-center">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase ${t.transaction_type === 'deposit' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800'}">
                                    ${t.transaction_type}
                                </span>
                            </td>
                            <td class="py-2.5 px-3 text-right font-black ${t.transaction_type === 'deposit' ? 'text-emerald-700' : 'text-rose-700'}">
                                Rp ${Number(t.amount).toLocaleString('id-ID')}
                            </td>
                        </tr>
                    `;
                }).join('');
            }

            document.getElementById('modalAuditDetail').classList.remove('hidden');
        }
    }

    // ================= APPROVE & VAULT TRANSFER (102) =================
    async function approveClosing(closingId, diff) {
        if (diff !== 0) {
            const confirm = await Swal.fire({
                title: 'PERINGATAN: Selisih Kas!',
                text: `Terdapat selisih kas sebesar Rp ${Math.abs(diff).toLocaleString('id-ID')}. Apakah Anda tetap ingin menyetujui dan memindahkan kas fisik ke Brankas Utama?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#f59e0b',
                confirmButtonText: 'Tetap Setujui',
                cancelButtonText: 'Batal & Periksa Ulang'
            });
            if (!confirm.isConfirmed) return;
        }

        Swal.fire({
            title: 'Otorisasi Brankas Utama (102)',
            text: 'Sistem akan membukukan jurnal pemindahan kas dari Kas Loket Teller (101) ke Brankas Utama Bank Mini (102).',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#059669',
            confirmButtonText: 'Setujui & Pindahkan Kas',
            cancelButtonText: 'Batal'
        }).then(async (result) => {
            if (result.isConfirmed) {
                const res = await fetchAPI(`/supervisor/closings/${closingId}/approve`, { method: 'POST' });
                if (res && res.data && res.data.success) {
                    Swal.fire('Disetujui!', res.data.message, 'success');
                    loadSupervisorClosings();
                } else {
                    Swal.fire('Gagal!', (res && res.data && res.data.message) || 'Gagal memproses approval.', 'error');
                }
            }
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        loadSupervisorClosings();
    });
</script>
@endsection
