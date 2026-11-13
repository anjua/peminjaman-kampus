document.addEventListener("DOMContentLoaded", () => {
    const el = document.getElementById("tabel-peminjaman");
    const filter = { status: "", dari: "", sampai: "" };

    const tabel = $(el).DataTable({
        ajax: { url: el.dataset.url, data: (d) => Object.assign(d, filter) },
        columns: [
            {
                data: "tanggal",
                render: (d, t, r) =>
                    t === "sort"
                        ? r.waktu_mulai
                        : `<div class="fw-semibold">${d}</div><small class="text-muted">${r.jam}</small>`,
            },
            {
                data: null,
                render: (r) =>
                    `${escapeHtml(r.ruang || "")}${r.ruang && r.alat ? "<br>" : ""}<small class="text-muted">${escapeHtml(r.alat)}</small>`,
            },
            {
                data: "keperluan",
                render: (d) =>
                    `<span class="text-truncate d-inline-block" style="max-width: 220px">${escapeHtml(d)}</span>`,
            },
            {
                data: "status_label",
                render: (d, t, r) => badgeStatus(d, r.status_warna),
            },
            {
                data: null,
                orderable: false,
                searchable: false,
                render: (r) => `<div class="d-flex gap-1">
          <a href="${r.url}" class="btn btn-sm btn-icon btn-label-primary" title="Detail"><i class="ti ti-eye"></i></a>
          ${r.boleh_batal ? `<button class="btn btn-sm btn-icon btn-label-danger batal" data-url="${r.url_batal}" title="Batalkan"><i class="ti ti-x"></i></button>` : ""}
        </div>`,
            },
        ],
        order: [[0, "desc"]],
        dom: domTabel,
        language: bahasaTabel,
        buttons: [],
        responsive: true,
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

    $(el).on("click", ".batal", async function () {
        if (
            !(await konfirmasi(
                "Batalkan peminjaman ini?",
                "Slot akan dilepas untuk orang lain.",
                "Ya, batalkan",
                "warning",
            ))
        )
            return;
        await kirim(this.dataset.url, "PATCH", null, this);
        tabel.ajax.reload(null, false);
    });
});
