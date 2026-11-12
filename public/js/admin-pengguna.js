document.addEventListener("DOMContentLoaded", () => {
    const el = document.getElementById("tabel-pengguna");
    const form = document.getElementById("form-pengguna");
    const offcanvas = new bootstrap.Offcanvas("#offcanvas-pengguna");
    const filter = { peran_id: "" };
    let urlSimpan = null;

    const tabel = $(el).DataTable({
        ajax: { url: el.dataset.url, data: (d) => Object.assign(d, filter) },
        columns: [
            {
                data: "name",
                render: (d, t, r) => `<div class="d-flex align-items-center">
          <span class="avatar avatar-sm me-2"><span class="avatar-initial rounded-circle bg-label-primary">${escapeHtml(r.inisial)}</span></span>
          <div><span class="fw-semibold">${escapeHtml(d)}</span><br><small class="text-muted">${escapeHtml(r.email)}</small></div></div>`,
            },
            { data: "nim_nip", render: (d) => escapeHtml(d || "-") },
            {
                data: "peran",
                render: (d) =>
                    `<span class="badge bg-label-primary">${escapeHtml(d || "-")}</span>`,
            },
            { data: "peminjaman_count" },
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
                render: () =>
                    '<button class="btn btn-sm btn-icon btn-label-primary ubah"><i class="ti ti-edit"></i></button>',
            },
        ],
        dom: domTabel,
        language: bahasaTabel,
        responsive: true,
        buttons: [], 
    });

    document.getElementById("filter-peran").addEventListener("change", (e) => {
        filter.peran_id = e.target.value;
        tabel.ajax.reload();
    });

    $(el).on("click", ".ubah", function () {
        const r = tabel.row($(this).closest("tr")).data();
        bersihkanError(form);
        urlSimpan = r.url;
        document.getElementById("judul-form").textContent = r.name;
        ["peran_id", "nim_nip", "telepon"].forEach(
            (k) => (form[k].value = r[k] ?? ""),
        );
        form.aktif.checked = r.aktif;
        offcanvas.show();
    });

    form.addEventListener("submit", async (e) => {
        e.preventDefault();
        const data = Object.fromEntries(new FormData(form));
        data.aktif = form.aktif.checked;
        await kirim(
            urlSimpan,
            "PUT",
            data,
            form.querySelector("[type=submit]"),
        );
        offcanvas.hide();
        tabel.ajax.reload(null, false);
    });
});
