document.addEventListener("DOMContentLoaded", () => {
    const el = document.getElementById("tabel-alat");
    const form = document.getElementById("form-alat");
    const offcanvas = new bootstrap.Offcanvas("#offcanvas-alat");
    let urlSimpan = null;

    const tabel = $(el).DataTable({
        ajax: el.dataset.url,
        columns: [
            {
                data: "nama",
                render: (d, t, r) =>
                    `<span class="fw-semibold">${escapeHtml(d)}</span><br><small class="text-muted">${escapeHtml(r.kode)}</small>`,
            },
            { data: "stok", render: (d) => `${d} unit` },
            {
                data: "dipinjam_sekarang",
                render: (d, t, r) => `${d} / ${r.stok}`,
            },
            { data: "kondisi", render: (d) => escapeHtml(KONDISI[d] ?? d) },
            {
                data: "denda_per_jam",
                render: (d) =>
                    d === null
                        ? `<span class="text-muted">${rupiah(TARIF_UMUM)} (umum)</span>`
                        : rupiah(d),
            },
            {
                data: "aktif",
                render: (d) =>
                    d
                        ? badgeStatus("Aktif", "success")
                        : badgeStatus("Nonaktif", "secondary"),
            },
            {
                data: null,
                orderable: false,
                searchable: false,
                render: (r) => `<div class="d-flex gap-1">
          <button class="btn btn-sm btn-icon btn-label-primary ubah" title="Ubah"><i class="ti ti-edit"></i></button>
          <button class="btn btn-sm btn-icon btn-label-${r.aktif ? "danger" : "success"} aktif" title="${r.aktif ? "Nonaktifkan" : "Aktifkan"}">
            <i class="ti ti-${r.aktif ? "ban" : "check"}"></i></button></div>`,
            },
        ],
        dom: domTabel,
        language: bahasaTabel,
        responsive: true,
        buttons: [
            {
                text: '<i class="ti ti-plus me-1"></i> Tambah Alat',
                className: "btn btn-primary",
                action: () => bukaForm(),
            },
        ],
    });

    function bukaForm(data = null) {
        form.reset();
        bersihkanError(form);
        urlSimpan = data ? `${el.dataset.url}/${data.id}` : null;
        document.getElementById("judul-form").textContent = data
            ? "Ubah Alat"
            : "Tambah Alat";
        if (data) {
            ["kode", "nama", "stok", "kondisi", "denda_per_jam"].forEach(
                (k) => (form[k].value = data[k] ?? ""),
            );
            form.aktif.checked = data.aktif;
        }
        offcanvas.show();
    }

    form.addEventListener("submit", async (e) => {
        e.preventDefault();
        const data = Object.fromEntries(new FormData(form));
        data.aktif = form.aktif.checked;
        data.denda_per_jam =
            data.denda_per_jam === "" ? null : data.denda_per_jam;
        await kirim(
            urlSimpan || form.dataset.urlTambah,
            urlSimpan ? "PUT" : "POST",
            data,
            form.querySelector("[type=submit]"),
        );
        offcanvas.hide();
        tabel.ajax.reload(null, false);
    });

    $(el).on("click", ".ubah", function () {
        bukaForm(tabel.row($(this).closest("tr")).data());
    });
    $(el).on("click", ".aktif", async function () {
        const r = tabel.row($(this).closest("tr")).data();
        if (
            r.aktif &&
            !(await konfirmasi(
                `Nonaktifkan ${r.nama}?`,
                "Alat tidak bisa dipilih untuk pengajuan baru.",
                "Ya, nonaktifkan",
                "warning",
            ))
        )
            return;
        await kirim(`${el.dataset.url}/${r.id}/aktif`, "PATCH", null, this);
        tabel.ajax.reload(null, false);
    });
});
