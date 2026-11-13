document.addEventListener("DOMContentLoaded", () => {
    const el = document.getElementById("tabel-persetujuan");

    const tabel = $(el).DataTable({
        ajax: el.dataset.url,
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
                    `${escapeHtml(r.ruang || "")}${r.ruang ? ` <small class="text-muted">(${r.jumlah_peserta} org)</small>` : ""}${r.alat ? `<br><small class="text-muted">${escapeHtml(r.alat)}</small>` : ""}`,
            },
            {
                data: "keperluan",
                render: (d) =>
                    `<span class="text-truncate d-inline-block" style="max-width: 220px" title="${escapeHtml(d)}">${escapeHtml(d)}</span>`,
            },
            {
                data: null,
                orderable: false,
                searchable: false,
                render: (r) => `<div class="d-flex gap-1">
          <a href="${r.url}" class="btn btn-sm btn-icon btn-label-secondary" title="Detail"><i class="ti ti-eye"></i></a>
          <button class="btn btn-sm btn-success setujui" data-url="${r.url_setujui}"><i class="ti ti-check me-1"></i>Setujui</button>
          <button class="btn btn-sm btn-label-danger tolak" data-url="${r.url_tolak}"><i class="ti ti-x me-1"></i>Tolak</button></div>`,
            },
        ],
        order: [[1, "asc"]],
        dom: domTabel,
        language: {
            ...bahasaTabel,
            emptyTable:
                '<div class="kosong"><i class="ti ti-mood-happy"></i>Tidak ada pengajuan yang menunggu.</div>',
        },
        buttons: [],
        responsive: true,
    });

    tabel.on("xhr", () => {
        const jumlah = tabel.ajax.json()?.data.length ?? 0;
        const badge = document.getElementById("badge-menunggu");
        if (badge) {
            badge.textContent = jumlah;
            badge.classList.toggle("d-none", jumlah === 0);
        }
    });

    $(el).on("click", ".setujui", async function () {
        const r = tabel.row($(this).closest("tr")).data();
        if (
            !(await konfirmasi(
                "Setujui peminjaman ini?",
                `${r.peminjam} · ${r.tanggal} ${r.jam}`,
                "Ya, setujui",
            ))
        )
            return;
        await kirim(this.dataset.url, "PATCH", null, this);
        tabel.ajax.reload(null, false);
    });

    $(el).on("click", ".tolak", async function () {
        const url = this.dataset.url;
        const hasil = await Swal.fire({
            title: "Tolak peminjaman?",
            input: "textarea",
            inputLabel: "Alasan (dikirim ke peminjam)",
            inputPlaceholder: "Contoh: ruang dipakai ujian",
            showCancelButton: true,
            confirmButtonText: "Tolak",
            cancelButtonText: "Batal",
            customClass: {
                confirmButton: "btn btn-danger me-3",
                cancelButton: "btn btn-label-secondary",
            },
            buttonsStyling: false,
            showLoaderOnConfirm: true,
            preConfirm: async (alasan) => {
                try {
                    return await kirim(url, "PATCH", { catatan_admin: alasan });
                } catch (e) {
                    Swal.showValidationMessage(
                        e.errors?.catatan_admin?.[0] ||
                            e.message ||
                            "Gagal menolak.",
                    );
                }
            },
            allowOutsideClick: () => !Swal.isLoading(),
        });
        if (hasil.isConfirmed) tabel.ajax.reload(null, false);
    });
});
