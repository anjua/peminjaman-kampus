document.addEventListener("DOMContentLoaded", () => {
    const el = document.getElementById("kalender");
    const $ruang = $("#filter-ruang");
    $ruang.select2({
        placeholder: $ruang.data("placeholder"),
        allowClear: true,
    });

    const kalender = new Calendar(el, {
        plugins: [dayGridPlugin, interactionPlugin, listPlugin, timegridPlugin],
        initialView: window.innerWidth < 768 ? "listWeek" : "timeGridWeek",
        locale: "id",
        firstDay: 1,
        slotMinTime: "06:00:00",
        slotMaxTime: "22:00:00",
        allDaySlot: false,
        headerToolbar: {
            start: "prev,next today",
            center: "title",
            end: "dayGridMonth,timeGridWeek,listWeek",
        },
        buttonText: {
            today: "Hari ini",
            month: "Bulan",
            week: "Minggu",
            list: "Daftar",
        },
        events: {
            url: el.dataset.url,
            extraParams: () => ({ ruang_id: $ruang.val() || "" }),
        },
        eventClassNames: (info) => [
            `fc-event-${info.event.extendedProps.calendar}`,
        ],
        eventDidMount: (info) =>
            info.el.setAttribute("title", info.event.extendedProps.status),
    });
    kalender.render();
    $ruang.on("change", () => kalender.refetchEvents());
});
