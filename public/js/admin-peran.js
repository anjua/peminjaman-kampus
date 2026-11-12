document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("form-peran");
    const modal = new bootstrap.Modal("#modal-peran");
    const semua = document.getElementById("pilih-semua");
    const hak = () => [...form.querySelectorAll(".hak")];
    const tombolHapus = document.getElementById("hapus-peran");
    let peran = null;

    function buka(data = null) {
        peran = data;
        form.reset();
        bersihkanError(form);
        document.getElementById("judul-peran").textContent = data
            ? `Ubah Peran ${data.nama}`
            : "Tambah Peran";
        tombolHapus.classList.toggle("d-none", !data);
        if (data) {
            ["kode", "nama", "keterangan"].forEach(
                (k) => (form[k].value = data[k] ?? ""),
            );
            hak().forEach(
                (c) => (c.checked = data.hak_akses.includes(Number(c.value))),
            );
        }
        semua.checked = hak().every((c) => c.checked);
        modal.show();
    }

    document
        .getElementById("tambah-peran")
        .addEventListener("click", () => buka());
    document
        .querySelectorAll(".ubah-peran")
        .forEach((a) =>
            a.addEventListener("click", () =>
                buka(JSON.parse(a.dataset.peran)),
            ),
        );

    semua.addEventListener("change", () =>
        hak().forEach((c) => (c.checked = semua.checked)),
    );
    form.addEventListener("change", (e) => {
        if (e.target.classList.contains("hak"))
            semua.checked = hak().every((c) => c.checked);
    });

    form.addEventListener("submit", async (e) => {
        e.preventDefault();
        const data = {
            kode: form.kode.value,
            nama: form.nama.value,
            keterangan: form.keterangan.value,
            hak_akses: hak()
                .filter((c) => c.checked)
                .map((c) => Number(c.value)),
        };
        await kirim(
            peran ? peran.url : form.dataset.urlTambah,
            peran ? "PUT" : "POST",
            data,
            form.querySelector("[type=submit]"),
        );
        setTimeout(() => location.reload(), 600); 
    });

    tombolHapus.addEventListener("click", async () => {
        if (
            !(await konfirmasi(
                `Hapus peran ${peran.nama}?`,
                "Peran yang masih dipakai pengguna tidak bisa dihapus.",
                "Ya, hapus",
                "warning",
            ))
        )
            return;
        await kirim(peran.url_hapus, "DELETE", null, tombolHapus);
        setTimeout(() => location.reload(), 600);
    });
});
