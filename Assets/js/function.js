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

// Alert function
  setTimeout(() => {
    document.querySelectorAll(".alert").forEach(el => {
      el.style.transition = "opacity 0.5s ease";
      el.style.opacity = "0";
      setTimeout(() => el.remove(), 500); // hapus dari DOM
    });
  }, 3000); // hilang setelah 3 detik

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

function generateQRpegawai(data, elementId, pegawaiId) {
    let qrContainer = document.getElementById(elementId);
    qrContainer.innerHTML = ""; // kosongkan dulu

    let qrcode = new QRCode(qrContainer, {
        text: data,
        width: 128,
        height: 128,
    });

    // Ambil canvas/gambar QR
    setTimeout(() => {
        let img = qrContainer.querySelector("img") || qrContainer.querySelector("canvas");
        if (img) {
            let qrBase64 = img.src || img.toDataURL("image/png");

            // Kirim QR ke server
            fetch("simpan-qr.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: "id=" + encodeURIComponent(pegawaiId) + "&qr=" + encodeURIComponent(qrBase64)
            })
            .then(res => res.text())
            .then(res => console.log("Server:", res))
            .catch(err => console.error(err));
        }
    }, 500); // kasih jeda biar QR selesai digambar
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
    if (video.videoWidth === 0 || video.videoHeight === 0) {
        requestAnimationFrame(scanQRCode);
        return;
    }

    const canvas = document.createElement("canvas");
    const context = canvas.getContext("2d");

    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;

    context.drawImage(video, 0, 0, canvas.width, canvas.height);

    const imageData = context.getImageData(0, 0, canvas.width, canvas.height);
    const code = jsQR(imageData.data, canvas.width, canvas.height);

    if (code) {
        updateStatus('green', 'QR Code Detected');
        console.log('QR Code Data:', code.data);

        // panggil function baru untuk proses absensi
        pindaiQRCode(code.data);

        // tampilkan data di UI
        const [name, id] = code.data.split(" | ");
        namaPegawai.textContent = name || "Unregistered";
        idPegawai.textContent = id || "Unregistered";

        const now = new Date();
        const tanggal = now.toLocaleDateString('id-ID', {
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

        if (lastQRCodeTime === null || (now - lastQRCodeTime) / 1000 > 8) {
            lastQRCodeTime = now; 

            namaPegawai.textContent = "nama pegawai";
            idPegawai.textContent = "ID";
            tanggalAbsen.textContent = "tanggal";
            waktuAbsen.textContent = "Waktu";  
            statusAbsen.textContent = "status";
            updateStatus('red', 'No QR Code Detected');
        }
        console.log('No QR code detected');
    }

    requestAnimationFrame(scanQRCode);
}

function updateStatus(color, message) {
    status.classList.remove("green", "red");
    status.classList.add(color);
    status.textContent = message;
}

function pindaiQRCode(data) {
  console.log("QR Data:", data);

  const parts = data.split("|");
  if (parts.length < 2) {
    alert("QR tidak valid");
    return;
  }

  const idPegawai = parts[1].trim();

  // 🔹 Ambil mode check-in / check-out
  const mode = document.querySelector('input[name="mode"]:checked').value;

  fetch("proses-absensi.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: "id=" + encodeURIComponent(idPegawai) + "&mode=" + encodeURIComponent(mode)
  })
  .then(res => res.json())
  .then(response => {
    const statusEl = document.getElementById("status");
    statusEl.textContent = response.message;

    if (response.status === "success") {
      statusEl.style.backgroundColor = "green";
    } else if (response.status === "warning") {
      statusEl.style.backgroundColor = "orange";
    } else {
      statusEl.style.backgroundColor = "red";
    }

    setTimeout(() => {
      statusEl.textContent = "Waiting for QR Code...";
      statusEl.style.backgroundColor = "";
    }, 3000);
  })
  .catch(err => console.error("Error:", err));
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
