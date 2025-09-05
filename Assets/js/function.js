console.log("Function.js loaded");

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

// --- QR Code Scanner ---

//generate QR Code
function generateQRCode() {
    var name = document.getElementById("employeeName").value;
    var id = document.getElementById("employeeId").value;

    // Pastikan input tidak kosong
    if(name.trim() === "" || id.trim() === "") {
        alert("Nama dan ID Pegawai harus diisi!");
        return;
    }

    // Membuat string data untuk QR Code
    var data = name + " | " + id;

    // Hapus QR Code lama sebelum membuat yang baru
    document.getElementById("qrcode").innerHTML = "";
    
    // Menghasilkan QR Code baru
    var qrcode = new QRCode(document.getElementById("qrcode"), {
        text: data,
        width: 200,
        height: 200
    });
}

// QR Code Scanner
const video = document.getElementById('video');
const toggleBtn = document.getElementById('toggleBtn');
const qrResult = document.getElementById('qr-result');
const status = document.getElementById('status');

const namaPegawai = document.getElementById('namaPegawai');
const idPegawai = document.getElementById('idPegawai');
const tanggalAbsen = document.getElementById('tanggalAbsen');
const waktuAbsen = document.getElementById('waktuAbsen');
const statusAbsen = document.getElementById('statusAbsen');

let stream = null;  // Store the camera stream
let isCameraOn = false;

async function startCamera() {
    try {
        stream = await navigator.mediaDevices.getUserMedia({ video: true });
        video.srcObject = stream;
        isCameraOn = true;
        toggleBtn.textContent = "Turn Off Camera";
        toggleBtn.classList.remove("off");
        toggleBtn.classList.add("on");

        // Tunggu hingga metadata video dimuat
        video.onloadedmetadata = function() {
            // Mulai pemindaian setelah metadata video dimuat
            requestAnimationFrame(scanQRCode);  
        };
    } catch (err) {
        alert("Tidak dapat mengakses kamera: " + err);
    }
}

let qrDetectionTimeout = null; // Menyimpan timeout untuk pengecekan delay
let lastQRCodeTime = null; // Waktu terakhir QR Code terdeteksi

function scanQRCode() {
    // Pastikan video sudah diputar dan ukuran valid
    if (video.videoWidth === 0 || video.videoHeight === 0) {
        requestAnimationFrame(scanQRCode);  // Tunggu hingga ukuran video valid
        return;
    }

    const canvas = document.createElement("canvas");
    const context = canvas.getContext("2d");

    // Set the canvas size to match the video dimensions
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;

    // Draw the current frame from the video onto the canvas
    context.drawImage(video, 0, 0, canvas.width, canvas.height);

    // Get the image data from the canvas
    const imageData = context.getImageData(0, 0, canvas.width, canvas.height);
    const code = jsQR(imageData.data, canvas.width, canvas.height);

    if (code) {
        updateStatus('green', 'QR Code Detected');
        console.log('QR Code Data:', code.data); 

        // Split the QR code data into name and ID
        const [name, id] = code.data.split(" | ");

        // Update the employee information on the page
        namaPegawai.textContent = name || "Unregistered";
        idPegawai.textContent = id || "Unregistered";

        const now = new Date();
        const tanggal = now.toLocaleDateString   ('id-ID', {
            day: '2-digit',
            month: 'long',
            year: 'numeric'
        });
        const waktu = now.toLocaleTimeString('id-ID', {
             hour: '2-digit', 
             minute: '2-digit',
        });

        tanggalAbsen.textContent = tanggal;
        waktuAbsen.textContent = `${waktu} WIB`;
        statusAbsen.textContent = "HADIR";
    }
    else {

        const now = new Date();
        const detik = now.getSeconds();

        // Cek apakah sudah lebih dari 5 detik sejak terakhir QR code terdeteksi
        if (lastQRCodeTime === null || (now - lastQRCodeTime) / 1000 > 8) {
            lastQRCodeTime = now; 

            // Reset informasi pegawai setelah 5 detik tidak ada QR code terdeteksi
            namaPegawai.textContent = "nama pegawai";
            idPegawai.textContent = "ID";
            tanggalAbsen.textContent = "tanggal";
            waktuAbsen.textContent = "Waktu";  
            statusAbsen.textContent = "status";
            updateStatus('red', 'No QR Code Detected');
        }

        console.log('No QR code detected');
    }

        // Keep scanning by calling scanQRCode again in the next frame
        requestAnimationFrame(scanQRCode);
    }

function updateStatus(color, message) {
    status.classList.remove("green", "red");
    status.classList.add(color);
    status.textContent = message;
}

function stopCamera() {
    if (stream) {
        const tracks = stream.getTracks();
        tracks.forEach(track => track.stop());
        video.srcObject = null;
    }
    isCameraOn = false;
    toggleBtn.textContent = "Turn On Camera";
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
