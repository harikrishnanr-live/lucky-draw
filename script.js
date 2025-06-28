let participants = [];

$(document).ready(function () {
  $("#userDisplay").text(localStorage.getItem("user") || "Guest");

  const dropzone = $("#dropzone");

  dropzone.on("click", () => $("#excelInput").click());
  dropzone.on("dragover", (e) => {
    e.preventDefault();
    dropzone.addClass("dragover");
  });
  dropzone.on("dragleave", () => dropzone.removeClass("dragover"));
  dropzone.on("drop", (e) => {
    e.preventDefault();
    dropzone.removeClass("dragover");
    handleFile(e.originalEvent.dataTransfer.files[0]);
  });

  $("#excelInput").on("change", function () {
    handleFile(this.files[0]);
  });

  $("#drawBtn").on("click", () => {
    if (participants.length === 0) {
      $("#result").html(`<div class="alert alert-warning">📂 Please upload a valid Excel file first.</div>`);
      return;
    }

    const prizes = [10, 9, 8, 7, 6, 5, 4, 3, 2, 1];
    const winners = [...participants].sort(() => 0.5 - Math.random()).slice(0, prizes.length);

    // Start fireworks
    const container = document.getElementById("fireworksCanvas");
    const fireworks = new Fireworks(container, {
      intensity: 40,
      speed: 2,
      delay: { min: 15, max: 25 }
    });
    fireworks.start();

    let html = `<h5>🎊 Lucky Winners</h5><ol id="winnerList" class="list-group list-group-numbered">`;
    html += `</ol>`;
    $("#result").html(html);

    winners.forEach((p, i) => {
      setTimeout(() => {
        const badge = `<span class="badge bg-primary rounded-pill">₹${prizes[i]}</span>`;
        $("#winnerList").append(
          `<li class="list-group-item d-flex justify-content-between align-items-center">
             ${p.code} (${p.name}) ${badge}
           </li>`
        );
        if (i === winners.length - 1) {
          setTimeout(() => fireworks.stop(), 3000);
        }
      }, i * 1500); // 1.5s delay between each winner
    });
  });
});

function handleFile(file) {
  if (!file || !file.name.endsWith(".xlsx")) {
    $("#result").html(`<div class="alert alert-danger">❌ Only .xlsx files are supported.</div>`);
    return;
  }

  const reader = new FileReader();
  reader.onload = function (e) {
    const data = new Uint8Array(e.target.result);
    const workbook = XLSX.read(data, { type: "array" });
    const sheet = workbook.Sheets[workbook.SheetNames[0]];
    const rows = XLSX.utils.sheet_to_json(sheet, { header: 1 });

    participants = rows.slice(1).map(row => ({
      code: row[0],
      name: row[1] || "Anonymous"
    }));

    $("#result").html(`<div class="alert alert-success">✅ ${participants.length} entries loaded!</div>`);
  };
  reader.readAsArrayBuffer(file);
}
