document.addEventListener("DOMContentLoaded", () => {
    const el = document.getElementById("tabel-periode");
    const form = document.getElementById("form-periode");
    const offcanvas = new bootstrap.Offcanvas("#offcanvas-periode");
    const tanggal = flatpickr(".tanggal", {
        dateFormat: "Y-m-d",
        altInput: true,
        altFormat: "d F Y",
    });
    let urlSimpan = null;
    const formatTgl = (d) =>
        new Date(d + "T00:00:00").toLocaleDateString("id-ID", {
            day: "2-digit",
            month: "long",
            year: "numeric",
        });

    const tabel = $(el).DataTable({
        ajax: el.dataset.url,
        columns: [
            {
                data: "nama",
                render: (d) =>
                    `<span class="fw-semibold">${escapeHtml(d)}</span>`,
            },
            {
                data: "tanggal_mulai",
                render: (d, t) => (t === "display" ? formatTgl(d) : d),
            },
            {
                data: "tanggal_selesai",
                render: (d, t) => (t === "display" ? formatTgl(d) : d),
            },
            {
                data: "aktif",
                render: (d) =>
                    d
                        ? badgeStatus("Tampil", "success")
                        : badgeStatus("Disembunyikan", "secondary"),
            },
            {
                data: null,
                orderable: false,
                searchable: false,
                render: () => `<div class="d-flex gap-1">
          <button class="btn btn-sm btn-icon btn-label-primary ubah" title="Ubah"><i class="ti ti-edit"></i></button>
          <button class="btn btn-sm btn-icon btn-label-danger hapus" title="Hapus"><i class="ti ti-trash"></i></button></div>`,
            },
        ],
        order: [[1, "desc"]],
        dom: domTabel,
        language: bahasaTabel,
        responsive: true,
        buttons: [
            {
                text: '<i class="ti ti-plus me-1"></i> Tambah Periode',
                className: "btn btn-primary",
                action: () => bukaForm(),
            },
        ],
    });

    function bukaForm(data = null) {
        form.reset();
        bersihkanError(form);
        tanggal.forEach((f) => f.clear());
        urlSimpan = data ? `${el.dataset.url}/${data.id}` : null;
        document.getElementById("judul-form").textContent = data
            ? "Ubah Periode"
            : "Tambah Periode";
        if (data) {
            form.nama.value = data.nama;
            tanggal[0].setDate(data.tanggal_mulai);
            tanggal[1].setDate(data.tanggal_selesai);
            form.aktif.checked = data.aktif;
        }
        offcanvas.show();
    }

    form.addEventListener("submit", async (e) => {
        e.preventDefault();
        const data = Object.fromEntries(new FormData(form));
        data.aktif = form.aktif.checked;
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
    $(el).on("click", ".hapus", async function () {
        const r = tabel.row($(this).closest("tr")).data();
        if (
            !(await konfirmasi(
                `Hapus ${r.nama}?`,
                "Periode tidak lagi muncul di filter laporan.",
                "Ya, hapus",
                "warning",
            ))
        )
            return;
        await kirim(`${el.dataset.url}/${r.id}`, "DELETE", null, this);
        tabel.ajax.reload(null, false);
    });
});
