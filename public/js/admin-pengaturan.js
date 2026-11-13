document.addEventListener("DOMContentLoaded", () => {
    flatpickr(".jam", {
        enableTime: true,
        noCalendar: true,
        dateFormat: "H:i",
        time_24hr: true,
    });

    document.querySelectorAll(".form-pengaturan").forEach((form) => {
        form.addEventListener("submit", async (e) => {
            e.preventDefault();
            const data = new FormData(form);
            form.querySelectorAll(".form-check-input[type=checkbox]").forEach(
                (c) => data.set(c.name, c.checked ? "1" : "0"),
            );
            form.querySelectorAll("input[type=file]").forEach((f) => {
                if (!f.files.length) data.delete(f.name);
            });
            await kirim(
                form.dataset.url,
                "POST",
                data,
                form.querySelector("[type=submit]"),
            ).catch(() => {});
        });
    });

    const formJam = document.getElementById("form-jam");
    formJam.addEventListener("submit", async (e) => {
        e.preventDefault();
        const jam = {};
        formJam.querySelectorAll("tr[data-hari]").forEach((tr) => {
            jam[tr.dataset.hari] = {
                buka: tr.querySelector(".buka").value,
                tutup: tr.querySelector(".tutup").value,
                aktif: tr.querySelector(".aktif").checked,
            };
        });
        await kirim(
            formJam.dataset.url,
            "PUT",
            { jam },
            formJam.querySelector("[type=submit]"),
        ).catch(() => {});
    });

    const formLibur = document.getElementById("form-libur");
    const daftarLibur = document.getElementById("daftar-libur");
    flatpickr("#tanggal-libur", {
        dateFormat: "Y-m-d",
        altInput: true,
        altFormat: "l, d F Y",
        locale: { firstDayOfWeek: 1 },
    });
    formLibur.addEventListener("submit", async (e) => {
        e.preventDefault();
        try {
            const json = await kirim(
                formLibur.dataset.url,
                "POST",
                Object.fromEntries(new FormData(formLibur)),
                formLibur.querySelector("[type=submit]"),
            );
            daftarLibur.querySelector(".kosong")?.closest("tr").remove();
            const tgl = new Date(json.data.tanggal);
            daftarLibur.insertAdjacentHTML(
                "beforeend",
                `<tr>
        <td>${tgl.toLocaleDateString("id-ID", { weekday: "long", day: "2-digit", month: "long", year: "numeric" })}</td>
        <td>${escapeHtml(json.data.keterangan)}</td>
        <td class="text-end"><button type="button" class="btn btn-sm btn-icon btn-label-danger hapus-libur" data-url="${formLibur.dataset.url}/${json.data.id}"><i class="ti ti-trash"></i></button></td>
      </tr>`,
            );
            formLibur.reset();
            formLibur.querySelector("#tanggal-libur")._flatpickr.clear();
        } catch {}
    });
    daftarLibur.addEventListener("click", async (e) => {
        const tombol = e.target.closest(".hapus-libur");
        if (
            !tombol ||
            !(await konfirmasi(
                "Hapus hari libur ini?",
                "",
                "Ya, hapus",
                "warning",
            ))
        )
            return;
        await kirim(tombol.dataset.url, "DELETE", null, tombol);
        tombol.closest("tr").remove();
    });

    const formStatus = document.getElementById("form-status");
    const perbaruiPratinjau = (e) => {
        const tr = e.target.closest("tr");
        if (!tr) return;
        const badge = tr.querySelector(".pratinjau");
        badge.textContent = tr.querySelector(".label-status").value;
        badge.className = `badge bg-label-${tr.querySelector(".warna-status").value} pratinjau`;
    };
    formStatus.addEventListener("input", perbaruiPratinjau);
    formStatus.addEventListener("change", perbaruiPratinjau);
    formStatus.addEventListener("submit", async (e) => {
        e.preventDefault();
        const status = {};
        formStatus.querySelectorAll("tbody tr").forEach((tr) => {
            const kode = tr.querySelector("code").textContent;
            status[kode] = {
                label: tr.querySelector(".label-status").value,
                warna: tr.querySelector(".warna-status").value,
            };
        });
        await kirim(
            formStatus.dataset.url,
            "PUT",
            { status },
            formStatus.querySelector("[type=submit]"),
        ).catch(() => {});
    });
});
