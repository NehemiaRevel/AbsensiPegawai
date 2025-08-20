// --- Date Display ---
function getTimeParts() {
    const now = new Date();
    return {
        hours: now.getHours().toString().padStart(2, '0'),
        minutes: now.getMinutes().toString().padStart(2, '0'),
        seconds: now.getSeconds().toString().padStart(2, '0'),
        now
    };
}

function updateDateTime(){
    const { hours, minutes, seconds, now } = getTimeParts();
    // Format the time in HH:MM (24-hour format)
    const timeString = `${hours}:${minutes}`;

    // Format the date into local format (Indonesian)
    const dateString = now.toLocaleDateString   ('id-ID', {
        day: '2-digit',
        month: 'long',
        year: 'numeric'
    });

    // Combine the date and time
    const dateTimeString = `${dateString}, ${timeString}`;

    // Display the formatted date and time
    document.getElementById("todayDate").textContent = dateTimeString;
}

updateDateTime();
setInterval(updateDateTime, 1000);

// --- Camera Function ---
const video = document.getElementById('video');
const toggleBtn = document.getElementById('toggleBtn');
let stream = null;  // Menyimpan stream kamera
let isCameraOn = false;

async function startCamera() {
    try {
        stream = await navigator.mediaDevices.getUserMedia({    video: true });
        video.srcObject = stream;
        isCameraOn = true;
        toggleBtn.textContent = "Turn off Camera";
        toggleBtn.classList.remove("off");
        toggleBtn.classList.add("on");
    } catch (err) {
        alert("Tidak dapat mengakses kamera: " + err);
    }
}

function stopCamera() {
    if (stream) {
        const tracks = stream.getTracks();
        tracks.forEach(track => track.stop());
        video.srcObject = null;
    }
    isCameraOn = false;
    toggleBtn.textContent = "Turn on Camera";
    toggleBtn.classList.remove("on");
    toggleBtn.classList.add("off");
}

toggleBtn.addEventListener("click", () => {
    if (isCameraOn) {
        stopCamera();
    } else {
        startCamera();
    }
});

// --- QR Code Scanner ---

function GetDate(){
    const now = new Date();

    // Format the date into local format (Indonesian)
    const dateString = now.toLocaleDateString   ('id-ID', {
        day: '2-digit',
        month: 'long',
        year: 'numeric'
    });

    const tanggalPegawaiString = `Tanggal Hadir = ${dateString}`;
    document.getElementById("tanggalAbsen").textContent = tanggalPegawaiString;
}

function GetTime(){
    const { hours, minutes, seconds, now } = getTimeParts();

    // Format the time in HH:MM (24-hour format)
    const timeString = `${hours}:${minutes}:${seconds}`;

    const waktuPegawaiString = `Jam Hadir = ${timeString}`;

    // Display the formatted date and time
    document.getElementById("waktuAbsen").textContent = waktuPegawaiString;
}

function onScanSuccess(decodedText, decodedResult) {
    // Contoh format QR: "12345|Budi Santoso"
    const parts = decodedText.split("|");
    const id = parts[0] || "-";
    const nama = parts[1] || "-";

    GetDate();
    GetTime();
  }

  function onScanFailure(error) {
    // Boleh diabaikan atau tampilkan pesan error
    console.warn(`Scan gagal: ${error}`);
  }

  // Inisialisasi scanner
  let html5QrcodeScanner = new Html5QrcodeScanner(
    "reader", 
    { fps: 10, qrbox: 250 }
  );
  html5QrcodeScanner.render(onScanSuccess, onScanFailure);

  // Data yang akan dimasukkan ke QR
const dataPegawai = "12345|Budi Santoso";

// Generate QR ke dalam div #qrcode
new QRCode(document.getElementById("qrcode"), {
  text: dataPegawai,
  width: 200,
  height: 200
});