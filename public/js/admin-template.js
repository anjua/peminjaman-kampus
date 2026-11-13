document.addEventListener("DOMContentLoaded", () => {
    const el = document.getElementById("tabel-template");
    const form = document.getElementById("form-template");
    const offcanvas = new bootstrap.Offcanvas("#offcanvas-template");
    const namaKanal = { database: "Aplikasi", mail: "Email" };
    let urlSimpan = null;
    let kursor = "isi"; 

    const tabel = $(el).DataTable({
        ajax: el.dataset.url,
        columns: [
            {
                data: "nama",
                render: (d, t, r) => `<div class="d-flex align-items-center">
          <span class="avatar avatar-sm me-2"><span class="avatar-initial rounded-circle bg-label-${r.warna}"><i class="${escapeHtml(r.ikon)}"></i></span></span>
          <div><span class="fw-semibold">${escapeHtml(d)}</span><br><small class="text-muted">${escapeHtml(r.judul)}</small></div></div>`,
            },
            {
                data: "isi",
                render: (d) =>
                    `<span class="text-truncate d-inline-block" style="max-width: 260px">${escapeHtml(d)}</span>`,
            },
            {
                data: "kanal",
                render: (d) =>
                    d
                        .map(
                            (k) =>
                                `<span class="badge bg-label-info me-1">${namaKanal[k] ?? k}</span>`,
                        )
                        .join(""),
            },
            { data: "penerima", render: (d) => escapeHtml(PENERIMA[d] ?? d) },
            {
                data: "aktif",
                render: (d) =>
                    d
                        ? badgeStatus("Aktif", "success")
                        : badgeStatus("Mati", "secondary"),
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

    const isiPenanda = (teks) =>
        Object.entries(CONTOH || {}).reduce(
            (t, [k, v]) => t.replaceAll(k, v ?? ""),
            teks,
        );

    function pratinjau() {
        document.getElementById("pratinjau").innerHTML = `
      <div class="flex-shrink-0 me-3"><span class="avatar"><span class="avatar-initial rounded-circle bg-label-${form.warna.value}"><i class="${escapeHtml(form.ikon.value)}"></i></span></span></div>
      <div><h6 class="mb-1">${escapeHtml(isiPenanda(form.judul.value))}</h6><p class="mb-0 small">${escapeHtml(isiPenanda(form.isi.value))}</p></div>`;
    }

    function bukaForm(data) {
        bersihkanError(form);
        urlSimpan = `${el.dataset.url}/${data.id}`;
        document.getElementById("judul-form").textContent = data.nama;
        ["judul", "isi", "ikon", "warna", "penerima"].forEach(
            (k) => (form[k].value = data[k]),
        );
        form.aktif.checked = data.aktif;
        form.querySelectorAll(".kanal").forEach(
            (c) => (c.checked = data.kanal.includes(c.value)),
        );
        pratinjau();
        offcanvas.show();
    }

    form.judul.addEventListener("focus", () => (kursor = "judul"));
    form.isi.addEventListener("focus", () => (kursor = "isi"));
    form.addEventListener("input", pratinjau);
    form.addEventListener("change", pratinjau);

    document.getElementById("tombol-penanda").addEventListener("click", (e) => {
        const penanda = e.target.closest("[data-penanda]")?.dataset.penanda;
        if (!penanda) return;
        const input = form[kursor];
        const [awal, akhir] = [
            input.selectionStart ?? input.value.length,
            input.selectionEnd ?? input.value.length,
        ];
        input.value =
            input.value.slice(0, awal) + penanda + input.value.slice(akhir);
        input.focus();
        input.setSelectionRange(awal + penanda.length, awal + penanda.length);
        pratinjau();
    });

    form.addEventListener("submit", async (e) => {
        e.preventDefault();
        const data = {
            judul: form.judul.value,
            isi: form.isi.value,
            ikon: form.ikon.value,
            warna: form.warna.value,
            penerima: form.penerima.value,
            aktif: form.aktif.checked,
            kanal: [...form.querySelectorAll(".kanal:checked")].map(
                (c) => c.value,
            ),
        };
        await kirim(
            urlSimpan,
            "PUT",
            data,
            form.querySelector("[type=submit]"),
        );
        offcanvas.hide();
        tabel.ajax.reload(null, false);
    });

    $(el).on("click", ".ubah", function () {
        bukaForm(tabel.row($(this).closest("tr")).data());
    });
});
