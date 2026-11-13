document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("form-pengajuan");
    const wizard = document.querySelector(".wizard-icons-example");
    const stepper = new Stepper(wizard, { linear: true });
    const $ruang = $("#ruang_id");
    let cek = null; 

    const besok = new Date(Date.now() + ATURAN.jeda_jam * 3600 * 1000);
    const fpTanggal = flatpickr("#tanggal", {
        dateFormat: "Y-m-d",
        altInput: true,
        altFormat: "l, j F Y",
        minDate: besok.toISOString().slice(0, 10),
        maxDate: new Date().fp_incr(ATURAN.maks_hari),
        disable: [
            (d) => !ATURAN.hari_aktif.includes(d.getDay()),
            ...ATURAN.libur,
        ],
        onChange: () => perbaruiWaktu(),
    });
    const opsiJam = {
        enableTime: true,
        noCalendar: true,
        dateFormat: "H:i",
        time_24hr: true,
        minuteIncrement: 30,
        onChange: () => perbaruiWaktu(),
    };
    const fpMulai = flatpickr("#jam_mulai", opsiJam);
    const fpSelesai = flatpickr("#jam_selesai", opsiJam);

    function perbaruiWaktu() {
        const tgl = document.getElementById("tanggal").value;
        const mulai = document.getElementById("jam_mulai").value;
        const selesai = document.getElementById("jam_selesai").value;
        document.getElementById("waktu_mulai").value =
            tgl && mulai ? `${tgl} ${mulai}` : "";
        document.getElementById("waktu_selesai").value =
            tgl && selesai ? `${tgl} ${selesai}` : "";
        if (tgl && mulai && selesai) cekKetersediaan();
    }

    let timer;
    function cekKetersediaan() {
        clearTimeout(timer);
        timer = setTimeout(async () => {
            const info = document.getElementById("info-jam");
            try {
                cek = await ambil(form.dataset.urlCek, {
                    waktu_mulai: form.waktu_mulai.value,
                    waktu_selesai: form.waktu_selesai.value,
                    ruang_id: $ruang.val(),
                });
            } catch (e) {
                cek = null;
                info.className = "alert alert-danger mb-0 status-ketersediaan";
                info.textContent =
                    Object.values(e.errors || {})[0]?.[0] || "Jam belum valid.";
                return;
            }
            if (cek.jam_operasional) {
                fpMulai.set({
                    minTime: cek.jam_operasional.buka,
                    maxTime: cek.jam_operasional.tutup,
                });
                fpSelesai.set({
                    minTime: cek.jam_operasional.buka,
                    maxTime: cek.jam_operasional.tutup,
                });
            }
            info.className = "alert alert-info mb-0 status-ketersediaan";
            info.innerHTML = cek.libur
                ? `<i class="ti ti-beach me-1"></i> Tanggal ini libur: ${escapeHtml(cek.libur)}`
                : cek.jam_operasional
                  ? `<i class="ti ti-clock me-1"></i> Jam layanan hari ini ${cek.jam_operasional.buka}–${cek.jam_operasional.tutup}. Durasi minimal ${ATURAN.durasi_min} menit.`
                  : '<i class="ti ti-alert-triangle me-1"></i> Hari ini tidak melayani peminjaman.';
            tampilkanKetersediaan();
        }, 300);
    }

    function tampilkanKetersediaan() {
        if (!cek) return;
        $ruang.find("option[value]").each(function () {
            if (!this.value) return;
            const terpakai = cek.ruang_terpakai.includes(
                parseInt(this.value, 10),
            );
            this.textContent =
                this.textContent.replace(/ — terpakai$/, "") +
                (terpakai ? " — terpakai" : "");
        });
        const infoRuang = document.getElementById("info-ruang");
        if ($ruang.val()) {
            infoRuang.innerHTML = cek.ruang_tersedia
                ? '<div class="alert alert-success mb-0"><i class="ti ti-circle-check me-1"></i> Ruang tersedia pada jam ini.</div>'
                : `<div class="alert alert-danger mb-0"><i class="ti ti-alert-circle me-1"></i> Bentrok dengan: ${cek.bentrok.map((b) => `${b.mulai}–${b.selesai} (${b.status})`).join(", ")}</div>`;
        } else {
            infoRuang.innerHTML = "";
        }
        for (const a of cek.alat) {
            const kartu = document.querySelector(`[data-alat="${a.id}"]`);
            if (!kartu) continue;
            kartu.querySelector(".sisa-stok").textContent =
                `sisa ${a.tersisa} dari ${a.stok}`;
            kartu.querySelector(".sisa-stok").className =
                `badge sisa-stok bg-label-${a.tersisa ? "success" : "danger"}`;
            const input = kartu.querySelector(".jumlah-alat");
            input.max = a.tersisa;
            if (+input.value > a.tersisa) input.value = a.tersisa;
            kartu.classList.toggle("tidak-tersedia", a.tersisa === 0);
            kartu.classList.toggle("terpilih", +input.value > 0);
        }
    }

    $ruang.select2({
        placeholder: $ruang.data("placeholder"),
        allowClear: true,
        dropdownParent: $ruang.parent(),
    });
    $ruang.on("change", () => {
        const opsi = $ruang.find(":selected");
        if (opsi.data("kapasitas"))
            document.getElementById("jumlah_peserta").max =
                opsi.data("kapasitas");
        if (form.waktu_mulai.value && form.waktu_selesai.value)
            cekKetersediaan();
    });

    document.getElementById("daftar-alat").addEventListener("click", (e) => {
        const tombol = e.target.closest(".tambah, .kurang");
        if (!tombol) return;
        const kartu = tombol.closest(".kartu-pilihan");
        const input = kartu.querySelector(".jumlah-alat");
        const baru =
            +input.value + (tombol.classList.contains("tambah") ? 1 : -1);
        input.value = Math.max(0, Math.min(baru, +input.max));
        kartu.classList.toggle("terpilih", +input.value > 0);
    });

    wizard.querySelectorAll(".btn-next").forEach((btn) =>
        btn.addEventListener("click", () => {
            bersihkanError(form);
            const langkah = stepper._currentIndex;
            if (langkah === 0) {
                if (!form.waktu_mulai.value || !form.waktu_selesai.value)
                    return tampilkanError(
                        {
                            waktu_mulai: [
                                "Pilih tanggal, jam mulai, dan jam selesai.",
                            ],
                        },
                        form,
                    );
                if (form.waktu_selesai.value <= form.waktu_mulai.value)
                    return tampilkanError(
                        {
                            waktu_selesai: [
                                "Jam selesai harus setelah jam mulai.",
                            ],
                        },
                        form,
                    );
            }
            if (langkah === 1) {
                const adaAlat = [...form.querySelectorAll(".jumlah-alat")].some(
                    (i) => +i.value > 0,
                );
                if (!$ruang.val() && !adaAlat)
                    return tampilkanError(
                        { ruang_id: ["Pilih ruang atau minimal satu alat."] },
                        form,
                    );
                if ($ruang.val() && cek && !cek.ruang_tersedia)
                    return tampilkanError(
                        {
                            ruang_id: [
                                "Ruang bentrok pada jam ini. Pilih ruang atau jam lain.",
                            ],
                        },
                        form,
                    );
                isiRingkasan();
            }
            stepper.next();
        }),
    );
    wizard
        .querySelectorAll(".btn-prev")
        .forEach((btn) =>
            btn.addEventListener("click", () => stepper.previous()),
        );

    function isiRingkasan() {
        const alat = [...form.querySelectorAll(".kartu-pilihan")]
            .filter((k) => +k.querySelector(".jumlah-alat").value > 0)
            .map(
                (k) =>
                    `${k.querySelector(".jumlah-alat").value} × ${escapeHtml(k.querySelector("h6").textContent)}`,
            );
        document.getElementById("ringkasan").innerHTML = `
      <h6 class="mb-3">Ringkasan</h6>
      <p class="mb-1"><i class="ti ti-calendar me-1"></i> ${fpTanggal.altInput.value}</p>
      <p class="mb-1"><i class="ti ti-clock me-1"></i> ${form.querySelector("#jam_mulai").value}–${form.querySelector("#jam_selesai").value}</p>
      <p class="mb-1"><i class="ti ti-door me-1"></i> ${$ruang.val() ? escapeHtml($ruang.find(":selected").text()) : "Tanpa ruang"}</p>
      <p class="mb-1"><i class="ti ti-users me-1"></i> ${form.jumlah_peserta.value} peserta</p>
      <p class="mb-0"><i class="ti ti-presentation me-1"></i> ${alat.length ? alat.join(", ") : "Tanpa alat"}</p>`;
    }

    form.addEventListener("submit", async (e) => {
        e.preventDefault();
        const data = Object.fromEntries(new FormData(form));
        data.detail = [...form.querySelectorAll(".kartu-pilihan")]
            .map((k) => ({
                alat_id: k.querySelector('[name$="[alat_id]"]').value,
                jumlah: k.querySelector(".jumlah-alat").value,
            }))
            .filter((d) => +d.jumlah > 0);
        Object.keys(data)
            .filter((k) => k.startsWith("detail["))
            .forEach((k) => delete data[k]);
        try {
            const hasil = await kirim(
                form.action,
                "POST",
                data,
                form.querySelector(".btn-submit"),
            );
            setTimeout(() => (location.href = hasil.url), 900);
        } catch (err) {
            const kunci = Object.keys(err.errors || {})[0] || "";
            if (kunci.startsWith("waktu")) stepper.to(1);
            else if (
                kunci.startsWith("ruang") ||
                kunci.startsWith("detail") ||
                kunci === "jumlah_peserta"
            )
                stepper.to(2);
        }
    });
});
