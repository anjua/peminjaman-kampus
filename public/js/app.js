const csrf = document.querySelector('meta[name="csrf-token"]').content;

function toast(jenis, pesan) {
    const ikon =
        {
            success: "ti-circle-check",
            danger: "ti-alert-circle",
            warning: "ti-alert-triangle",
            info: "ti-info-circle",
        }[jenis] || "ti-bell";
    const el = document.createElement("div");
    el.className = `bs-toast toast fade bg-${jenis} animate__animated animate__fadeInRight`;
    el.setAttribute("role", "alert");
    el.innerHTML = `
    <div class="toast-header">
      <i class="ti ${ikon} ti-xs me-2"></i>
      <div class="me-auto fw-semibold">${jenis === "success" ? "Berhasil" : jenis === "danger" ? "Gagal" : "Info"}</div>
      <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
    </div>
    <div class="toast-body"></div>`;
    el.querySelector(".toast-body").textContent = pesan;
    document.getElementById("wadah-toast").appendChild(el);
    const t = new bootstrap.Toast(el, { delay: 4000 });
    el.addEventListener("hidden.bs.toast", () => el.remove());
    t.show();
}

function tampilkanError(errors, wadah = document) {
    wadah
        .querySelectorAll(".is-invalid")
        .forEach((el) => el.classList.remove("is-invalid"));
    wadah
        .querySelectorAll(".invalid-feedback.dari-server")
        .forEach((el) => el.remove());
    let pertama = null;
    for (const [field, pesan] of Object.entries(errors || {})) {
        const nama = field
            .split(".")
            .map((b, i) => (i === 0 ? b : `[${b}]`))
            .join("");
        const input =
            wadah.querySelector(`[name="${nama}"]`) ||
            wadah.querySelector(`[name="${nama}[]"]`) ||
            wadah.querySelector(`[data-error="${field}"]`);
        if (!input) {
            toast("danger", pesan[0]);
            continue;
        }
        input.classList.add("is-invalid");
        const div = document.createElement("div");
        div.className = "invalid-feedback dari-server d-block";
        div.textContent = pesan[0];
        const target =
            input.closest(".input-group") ||
            (input.classList.contains("select2-hidden-accessible")
                ? input.nextElementSibling
                : input);
        target.after(div);
        pertama ??= input;
    }
    pertama?.focus();
}

function bersihkanError(wadah = document) {
    tampilkanError({}, wadah);
}

async function kirim(url, method = "POST", data = null, tombol = null) {
    const isForm = data instanceof FormData;
    if (isForm && !["GET", "POST"].includes(method)) {
        data.append("_method", method); // Laravel membaca _method untuk PUT/PATCH/DELETE
        method = "POST";
    }
    tombol?.setAttribute("disabled", true);
    tombol?.insertAdjacentHTML(
        "afterbegin",
        '<span class="spinner-border spinner-border-sm me-1 penanda-loading"></span>',
    );
    try {
        const res = await fetch(url, {
            method,
            headers: {
                "X-CSRF-TOKEN": csrf,
                Accept: "application/json",
                "X-Requested-With": "XMLHttpRequest",
                ...(isForm || !data
                    ? {}
                    : { "Content-Type": "application/json" }),
            },
            body: data ? (isForm ? data : JSON.stringify(data)) : null,
        });
        const json = await res.json().catch(() => ({}));
        if (res.status === 422) {
            tampilkanError(
                json.errors,
                tombol?.closest("form, .modal, .offcanvas") || document,
            );
            if (!json.errors) toast("danger", json.message);
            throw json;
        }
        if (!res.ok) {
            toast("danger", json.message || "Terjadi kesalahan, coba lagi.");
            throw json;
        }
        if (json.pesan) toast("success", json.pesan);
        return json;
    } finally {
        tombol?.removeAttribute("disabled");
        tombol?.querySelector(".penanda-loading")?.remove();
    }
}

async function ambil(url, params = {}) {
    const q = new URLSearchParams();
    for (const [k, v] of Object.entries(params)) {
        if (Array.isArray(v)) v.forEach((x) => q.append(`${k}[]`, x));
        else if (v !== null && v !== undefined && v !== "") q.append(k, v);
    }
    const res = await fetch(`${url}${q.toString() ? "?" + q : ""}`, {
        headers: {
            Accept: "application/json",
            "X-Requested-With": "XMLHttpRequest",
        },
    });
    if (!res.ok) throw await res.json().catch(() => ({}));
    return res.json();
}

async function konfirmasi(
    judul,
    teks = "",
    tombol = "Ya, lanjutkan",
    ikon = "question",
) {
    const hasil = await Swal.fire({
        title: judul,
        text: teks,
        icon: ikon,
        showCancelButton: true,
        confirmButtonText: tombol,
        cancelButtonText: "Batal",
        customClass: {
            confirmButton: "btn btn-primary me-3",
            cancelButton: "btn btn-label-secondary",
        },
        buttonsStyling: false,
    });
    return hasil.isConfirmed;
}

const rupiah = (n) => "Rp" + Number(n || 0).toLocaleString("id-ID");
const badgeStatus = (label, warna) =>
    `<span class="badge bg-label-${warna}">${label}</span>`;
const escapeHtml = (s) =>
    String(s ?? "").replace(
        /[&<>"']/g,
        (c) =>
            ({
                "&": "&amp;",
                "<": "&lt;",
                ">": "&gt;",
                '"': "&quot;",
                "'": "&#39;",
            })[c],
    );

const bahasaTabel = {
    search: "",
    searchPlaceholder: "Cari...",
    lengthMenu: "_MENU_",
    info: "Menampilkan _START_–_END_ dari _TOTAL_ data",
    infoEmpty: "Tidak ada data",
    infoFiltered: "(disaring dari _MAX_)",
    zeroRecords: "Data tidak ditemukan",
    emptyTable: "Belum ada data",
    loadingRecords: "Memuat...",
    processing: "Memuat...",
    paginate: {
        next: '<i class="ti ti-chevron-right ti-sm"></i>',
        previous: '<i class="ti ti-chevron-left ti-sm"></i>',
    },
};
const domTabel =
    '<"card-header d-flex flex-wrap gap-2 justify-content-between align-items-center pb-md-0"<"d-flex align-items-center gap-2"l><"d-flex align-items-center gap-2"fB>>t<"row mx-2"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>';

async function muatNotifikasi() {
    const daftar = document.getElementById("daftar-notifikasi");
    if (!daftar) return;
    try {
        const json = await ambil(
            document.querySelector('meta[name="url-notifikasi"]').content,
        );
        const badge = document.getElementById("jumlah-notifikasi");
        badge.textContent = json.jumlah;
        badge.classList.toggle("d-none", json.jumlah === 0);
        daftar.innerHTML = json.data.length
            ? json.data
                  .map(
                      (n) => `
        <li class="list-group-item list-group-item-action dropdown-notifications-item ${n.dibaca ? "marked-as-read" : ""}">
          <a href="${n.url}" class="d-flex text-body">
            <div class="flex-shrink-0 me-3">
              <div class="avatar"><span class="avatar-initial rounded-circle bg-label-${n.warna}"><i class="${n.ikon}"></i></span></div>
            </div>
            <div class="flex-grow-1">
              <h6 class="mb-1">${escapeHtml(n.judul)}</h6>
              <p class="mb-0 small">${escapeHtml(n.pesan)}</p>
              <small class="text-muted">${n.waktu}</small>
            </div>
            ${n.dibaca ? "" : '<div class="flex-shrink-0 dropdown-notifications-actions"><span class="badge badge-dot bg-primary"></span></div>'}
          </a>
        </li>`,
                  )
                  .join("")
            : '<li class="list-group-item text-center text-muted py-4"><i class="ti ti-bell-off d-block mb-2 fs-3"></i>Belum ada notifikasi</li>';
    } catch (e) {
    }
}

document.addEventListener("DOMContentLoaded", () => {
    if (!document.getElementById("daftar-notifikasi")) return;
    muatNotifikasi();
    const detik = parseInt(
        document.querySelector('meta[name="polling-detik"]').content,
        10,
    );
    if (detik > 0) setInterval(muatNotifikasi, detik * 1000);
    document
        .getElementById("baca-semua")
        .addEventListener("click", async () => {
            await kirim(
                document.querySelector('meta[name="url-baca-semua"]').content,
                "PATCH",
            );
            muatNotifikasi();
        });
});

if (window.flatpickr) {
    flatpickr.localize({
        weekdays: {
            shorthand: ["Min", "Sen", "Sel", "Rab", "Kam", "Jum", "Sab"],
            longhand: [
                "Minggu",
                "Senin",
                "Selasa",
                "Rabu",
                "Kamis",
                "Jumat",
                "Sabtu",
            ],
        },
        months: {
            shorthand: [
                "Jan",
                "Feb",
                "Mar",
                "Apr",
                "Mei",
                "Jun",
                "Jul",
                "Agu",
                "Sep",
                "Okt",
                "Nov",
                "Des",
            ],
            longhand: [
                "Januari",
                "Februari",
                "Maret",
                "April",
                "Mei",
                "Juni",
                "Juli",
                "Agustus",
                "September",
                "Oktober",
                "November",
                "Desember",
            ],
        },
        firstDayOfWeek: 1,
        rangeSeparator: " s.d. ",
        time_24hr: true,
    });
}
