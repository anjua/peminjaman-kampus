document.addEventListener("DOMContentLoaded", () => {
    const el = document.getElementById("tabel-ruang");
    const form = document.getElementById("form-ruang");
    const offcanvas = new bootstrap.Offcanvas("#offcanvas-ruang");
    let urlSimpan = null; 

    const tabel = $(el).DataTable({
        ajax: el.dataset.url,
        columns: [
            {
                data: "nama",
                render: (d, t, r) => `<div class="d-flex align-items-center">
          ${r.foto_url ? `<img src="${r.foto_url}" class="rounded me-2 foto-ruang" alt="">` : '<div class="avatar me-2"><span class="avatar-initial rounded bg-label-primary"><i class="ti ti-door"></i></span></div>'}
          <div><span class="fw-semibold">${escapeHtml(d)}</span><br><small class="text-muted">${escapeHtml(r.kode)}</small></div></div>`,
            },
            { data: "lokasi", render: escapeHtml },
            { data: "kapasitas", render: (d) => `${d} orang` },
            { data: "peminjaman_count", render: (d) => `${d}×` },
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
                text: '<i class="ti ti-plus me-1"></i> Tambah Ruang',
                className: "btn btn-primary",
                action: () => bukaForm(),
            },
        ],
    });

    function bukaForm(data = null) {
        form.reset();
        bersihkanError(form);
        document.getElementById("pratinjau-foto").classList.add("d-none");
        urlSimpan = data ? `${el.dataset.url}/${data.id}` : null;
        document.getElementById("judul-form").textContent = data
            ? "Ubah Ruang"
            : "Tambah Ruang";
        if (data) {
            ["kode", "nama", "lokasi", "kapasitas", "fasilitas"].forEach(
                (k) => (form[k].value = data[k] ?? ""),
            );
            form.aktif.checked = data.aktif;
            if (data.foto_url) {
                const img = document.getElementById("pratinjau-foto");
                img.src = data.foto_url;
                img.classList.remove("d-none");
            }
        }
        offcanvas.show();
    }

    form.foto.addEventListener("change", () => {
        const file = form.foto.files[0];
        const img = document.getElementById("pratinjau-foto");
        if (file) {
            img.src = URL.createObjectURL(file);
            img.classList.remove("d-none");
        }
    });

    form.addEventListener("submit", async (e) => {
        e.preventDefault();
        const data = new FormData(form); 
        data.set("aktif", form.aktif.checked ? 1 : 0);
        if (!form.foto.files.length) data.delete("foto");
        await kirim(
            urlSimpan || form.dataset.urlTambah,
            "POST",
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
                "Ruang tidak bisa dipilih untuk pengajuan baru. Riwayat tetap tersimpan.",
                "Ya, nonaktifkan",
                "warning",
            ))
        )
            return;
        await kirim(`${el.dataset.url}/${r.id}/aktif`, "PATCH", null, this);
        tabel.ajax.reload(null, false);
    });
});
