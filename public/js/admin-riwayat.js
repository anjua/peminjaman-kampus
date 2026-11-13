document.addEventListener("DOMContentLoaded", () => {
    const el = document.getElementById("tabel-riwayat");
    const filter = { status: "", dari: "", sampai: "" };

    const tabel = $(el).DataTable({
        ajax: { url: el.dataset.url, data: (d) => Object.assign(d, filter) },
        columns: [
            {
                data: "peminjam",
                render: (d, t, r) =>
                    `<span class="fw-semibold">${escapeHtml(d)}</span><br><small class="text-muted">${escapeHtml(r.nim_nip || "")}</small>`,
            },
            {
                data: "tanggal",
                render: (d, t, r) =>
                    t === "sort"
                        ? r.waktu_mulai
                        : `${d}<br><small class="text-muted">${r.jam}</small>`,
            },
            {
                data: null,
                render: (r) =>
                    `${escapeHtml(r.ruang || "")}${r.alat ? `<br><small class="text-muted">${escapeHtml(r.alat)}</small>` : ""}`,
            },
            {
                data: "keperluan",
                render: (d) =>
                    `<span class="text-truncate d-inline-block" style="max-width: 200px">${escapeHtml(d)}</span>`,
            },
            {
                data: "status_label",
                render: (d, t, r) => badgeStatus(d, r.status_warna),
            },
            {
                data: "url",
                orderable: false,
                searchable: false,
                render: (d) =>
                    `<a href="${d}" class="btn btn-sm btn-icon btn-label-primary"><i class="ti ti-eye"></i></a>`,
            },
        ],
        order: [[1, "desc"]],
        dom: domTabel,
        language: bahasaTabel,
        responsive: true,
        // Tombol ekspor ala tables-datatables-advanced.html (Laporan lengkap ada di menu Laporan)
        buttons: [
            {
                extend: "collection",
                className: "btn btn-label-primary dropdown-toggle",
                text: '<i class="ti ti-download me-1"></i> Ekspor',
                buttons: [
                    {
                        extend: "print",
                        text: '<i class="ti ti-printer me-1"></i> Cetak',
                        className: "dropdown-item",
                        exportOptions: { columns: [0, 1, 2, 3, 4] },
                    },
                    {
                        extend: "csv",
                        text: '<i class="ti ti-file-text me-1"></i> CSV',
                        className: "dropdown-item",
                        exportOptions: { columns: [0, 1, 2, 3, 4] },
                    },
                    {
                        extend: "copy",
                        text: '<i class="ti ti-copy me-1"></i> Salin',
                        className: "dropdown-item",
                        exportOptions: { columns: [0, 1, 2, 3, 4] },
                    },
                ],
            },
        ],
    });

    document.getElementById("filter-status").addEventListener("change", (e) => {
        filter.status = e.target.value;
        tabel.ajax.reload();
    });
    flatpickr("#filter-tanggal", {
        mode: "range",
        dateFormat: "Y-m-d",
        onChange: (tgl) => {
            if (tgl.length === 1) return;
            [filter.dari, filter.sampai] = tgl.map((t) =>
                flatpickr.formatDate(t, "Y-m-d"),
            );
            tabel.ajax.reload();
        },
    });
});
