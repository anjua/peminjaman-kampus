document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("filter-laporan");
    let grafikHarian = null;
    let grafikRuang = null;

    $(".select2").each(function () {
        $(this).select2({
            placeholder: this.dataset.placeholder,
            allowClear: true,
            dropdownParent: $(this).parent(),
        });
    });

    const rentang = flatpickr("#rentang", {
        mode: "range",
        dateFormat: "Y-m-d",
        onChange: (tgl) => {
            if (tgl.length === 1) return;
            [form.dari.value, form.sampai.value] = tgl.map((t) =>
                flatpickr.formatDate(t, "Y-m-d"),
            );
            form.periode_id.value = ""; 
            muat();
        },
    });

    const tabel = $("#tabel-laporan").DataTable({
        data: [],
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
                    `${escapeHtml(r.ruang || "")}${r.alat ? `<br><small class="text-muted">${escapeHtml(r.alat)}</small>` : ""}`,
            },
            {
                data: "status_label",
                render: (d, t, r) => badgeStatus(escapeHtml(d), r.status_warna),
            },
            {
                data: "denda",
                render: (d, t, r) =>
                    t === "sort"
                        ? d
                        : d
                          ? `${rupiah(d)}${r.denda_lunas ? "" : ' <span class="badge bg-label-danger">belum lunas</span>'}`
                          : "-",
            },
        ],
        dom: domTabel,
        language: bahasaTabel,
        responsive: true,
        buttons: [], 
    });

    function nilaiFilter() {
        const hasil = {};
        for (const el of form.elements) {
            if (!el.name) continue;
            hasil[el.name] = el.multiple
                ? [...el.selectedOptions].map((o) => o.value)
                : el.value;
        }
        return hasil;
    }

    async function muat() {
        document.getElementById("kartu-status").innerHTML =
            '<div class="col-12"><div class="placeholder-glow"><span class="placeholder col-12 rounded" style="height: 90px"></span></div></div>';
        try {
            const d = await ambil(form.dataset.url, nilaiFilter());
            document.getElementById("judul-periode").textContent =
                `${d.judul} · ${d.total} peminjaman · denda ${rupiah(d.total_denda)}`;
            kartu(d.per_status);
            grafik(d);
            tabel.clear().rows.add(d.baris).draw();
        } catch (e) {
            toast("danger", e.message || "Gagal memuat laporan.");
        }
    }

    function kartu(perStatus) {
        document.getElementById("kartu-status").innerHTML = perStatus
            .map(
                (s) => `
      <div class="col-sm-6 col-lg">
        <div class="card card-border-shadow-${s.warna} h-100">
          <div class="card-body">
            <div class="d-flex align-items-center mb-2 pb-1">
              <div class="avatar me-2"><span class="avatar-initial rounded bg-label-${s.warna}"><i class="ti ti-clipboard-list ti-md"></i></span></div>
              <h4 class="ms-1 mb-0">${s.jumlah}</h4>
            </div>
            <p class="mb-0">${escapeHtml(s.label)}</p>
          </div>
        </div>
      </div>`,
            )
            .join("");
    }

    function grafik(d) {
        const warnaUtama =
            getComputedStyle(document.documentElement)
                .getPropertyValue("--bs-primary")
                .trim() || "#7367f0";
        const opsiHarian = {
            chart: { type: "area", height: 300, toolbar: { show: false } },
            series: [{ name: "Peminjaman", data: d.per_hari.jumlah }],
            xaxis: { categories: d.per_hari.tanggal, type: "datetime" },
            colors: [warnaUtama],
            dataLabels: { enabled: false },
            stroke: { curve: "smooth", width: 2 },
            noData: { text: "Tidak ada data" },
        };
        const opsiRuang = {
            chart: { type: "bar", height: 300, toolbar: { show: false } },
            series: [
                {
                    name: "Peminjaman",
                    data: d.ruang_teratas.map((r) => r.jumlah),
                },
            ],
            xaxis: { categories: d.ruang_teratas.map((r) => r.nama) },
            plotOptions: {
                bar: { horizontal: true, borderRadius: 6, barHeight: "50%" },
            },
            colors: [warnaUtama],
            noData: { text: "Tidak ada data" },
        };
        if (grafikHarian) {
            grafikHarian.updateOptions(opsiHarian);
            grafikRuang.updateOptions(opsiRuang);
        } else {
            grafikHarian = new ApexCharts(
                document.querySelector("#grafik-harian"),
                opsiHarian,
            );
            grafikRuang = new ApexCharts(
                document.querySelector("#grafik-ruang"),
                opsiRuang,
            );
            grafikHarian.render();
            grafikRuang.render();
        }
    }

    $(form).on("change", "select", () => {
        if (form.periode_id.value) {
            rentang.clear();
            form.dari.value = form.sampai.value = "";
        }
        muat();
    });

    ["unduh-pdf", "unduh-excel"].forEach((id) => {
        document.getElementById(id).addEventListener("click", (e) => {
            const q = new URLSearchParams();
            for (const [k, v] of Object.entries(nilaiFilter())) {
                if (Array.isArray(v)) v.forEach((x) => q.append(`${k}[]`, x));
                else if (v) q.append(k, v);
            }
            const kolom = [
                ...document.querySelectorAll("#pilihan-kolom input:checked"),
            ].map((c) => c.value);
            if (!kolom.length) {
                e.preventDefault();
                toast("warning", "Pilih minimal satu kolom.");
                return;
            }
            kolom.forEach((k) => q.append("kolom[]", k));
            e.currentTarget.href = `${e.currentTarget.dataset.url}?${q}`;
        });
    });

    muat();
});
