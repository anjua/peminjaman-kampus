document.addEventListener("DOMContentLoaded", () => {
    const el = document.getElementById("tabel-pengembalian");
    const modal = new bootstrap.Modal("#modal-kembali");
    const form = document.getElementById("form-kembali");
    let tab = "belum";
    let barisAktif = null;

    const durasi = (menit) =>
        menit >= 60
            ? `${Math.floor(menit / 60)} jam ${menit % 60} menit`
            : `${menit} menit`;

    const tabel = $(el).DataTable({
        ajax: { url: el.dataset.url, data: (d) => Object.assign(d, { tab }) },
        columns: [
            {
                data: "peminjam",
                render: (d, t, r) =>
                    `<a href="${r.url}" class="fw-semibold">${escapeHtml(d)}</a><br><small class="text-muted">${escapeHtml(r.nim_nip || "")}</small>`,
            },
            { data: "alat", render: escapeHtml },
            {
                data: "tanggal",
                render: (d, t, r) =>
                    t === "sort"
                        ? r.waktu_selesai
                        : `${d}<br><small class="text-muted">${r.jam}</small>`,
            },
            {
                data: null,
                render: (r) =>
                    r.dikembalikan_pada
                        ? `Kembali ${r.dikembalikan_pada}<br><small class="text-muted">${escapeHtml(KONDISI[r.kondisi_kembali] ?? r.kondisi_kembali ?? "")}</small>`
                        : r.terlambat_menit > 0
                          ? `<span class="badge bg-label-danger">Terlambat ${durasi(r.terlambat_menit)}</span>`
                          : '<span class="badge bg-label-info">Sedang dipakai</span>',
            },
            {
                data: "denda",
                render: (d, t, r) =>
                    d
                        ? `${rupiah(d)} ${r.denda_lunas ? badgeStatus("Lunas", "success") : badgeStatus("Belum lunas", "danger")}`
                        : "-",
            },
            {
                data: null,
                orderable: false,
                searchable: false,
                render: (r) =>
                    tab === "belum"
                        ? '<button class="btn btn-sm btn-primary kembali"><i class="ti ti-arrow-back-up me-1"></i>Tandai kembali</button>'
                        : tab === "denda" && BOLEH_LUNAS
                          ? `<button class="btn btn-sm btn-success lunas" data-url="${r.url_lunas}"><i class="ti ti-cash me-1"></i>Tandai lunas</button>`
                          : "",
            },
        ],
        order: [[2, "asc"]],
        dom: domTabel,
        language: bahasaTabel,
        buttons: [],
        responsive: true,
    });

    document.querySelectorAll("[data-tab]").forEach((btn) =>
        btn.addEventListener("click", () => {
            document
                .querySelectorAll("[data-tab]")
                .forEach((b) => b.classList.toggle("active", b === btn));
            tab = btn.dataset.tab;
            tabel.ajax.reload();
        }),
    );

    $(el).on("click", ".kembali", async function () {
        barisAktif = tabel.row($(this).closest("tr")).data();
        form.reset();
        bersihkanError(form);
        document.getElementById("info-kembali").innerHTML =
            `<strong>${escapeHtml(barisAktif.peminjam)}</strong><br>${escapeHtml(barisAktif.alat)}`;
        const kotak = document.getElementById("perkiraan-denda");
        kotak.className = "alert alert-secondary mb-0";
        kotak.textContent = "Menghitung perkiraan denda...";
        modal.show();
        const { denda } = await ambil(barisAktif.url_perkiraan);
        kotak.className = `alert mb-0 alert-${denda ? "danger" : "success"}`;
        kotak.innerHTML = denda
            ? `<i class="ti ti-cash me-1"></i> Jika disimpan sekarang, denda <strong>${rupiah(denda)}</strong>.`
            : '<i class="ti ti-circle-check me-1"></i> Tidak ada denda.';
    });

    form.addEventListener("submit", async (e) => {
        e.preventDefault();
        await kirim(
            barisAktif.url_simpan,
            "PATCH",
            Object.fromEntries(new FormData(form)),
            form.querySelector("[type=submit]"),
        );
        modal.hide();
        tabel.ajax.reload(null, false);
    });

    $(el).on("click", ".lunas", async function () {
        if (
            !(await konfirmasi(
                "Tandai denda lunas?",
                "Pastikan pembayaran sudah diterima.",
                "Ya, lunas",
            ))
        )
            return;
        await kirim(this.dataset.url, "PATCH", null, this);
        tabel.ajax.reload(null, false);
    });
});
