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
// const video = document.getElementById('video');
// const toggleBtn = document.getElementById('toggleBtn');
// let stream = null;  // Menyimpan stream kamera
// let isCameraOn = false;

// async function startCamera() {
//     try {
//         stream = await navigator.mediaDevices.getUserMedia({    video: true });
//         video.srcObject = stream;
//         isCameraOn = true;
//         requestAnimationFrame(scanQRCode);
//         toggleBtn.textContent = "Turn off Camera";
//         toggleBtn.classList.remove("off");
//         toggleBtn.classList.add("on");
//     } catch (err) {
//         alert("Tidak dapat mengakses kamera: " + err);
//     }
// }

// function stopCamera() {
//     if (stream) {
//         const tracks = stream.getTracks();
//         tracks.forEach(track => track.stop());
//         video.srcObject = null;
//     }
//     isCameraOn = false;
//     toggleBtn.textContent = "Turn on Camera";
//     toggleBtn.classList.remove("on");
//     toggleBtn.classList.add("off");
// }

// toggleBtn.addEventListener("click", () => {
//     if (isCameraOn) {
//         stopCamera();
//     } else {
//         startCamera();
//     }
// });

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
    var data = "Nama: " + name + "\nID: " + id;

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
let stream = null;  // Store the camera stream
let isCameraOn = false;

async function startCamera() {
    try {
        stream = await navigator.mediaDevices.getUserMedia({ video: true });
        video.srcObject = stream;
        isCameraOn = true;
        toggleBtn.textContent = "Turn off Camera";
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

let qrDataTimeout = null;
let isQRCodeDetected = false; // Flag memastikan QR Code hanya diproses sekali

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

    // If a QR code is detected and it hasn't been processed before
    if (code && !isQRCodeDetected) {
        // Set the flag to prevent reprocessing the same QR Code
        isQRCodeDetected = true;

        // Display QR Code data and update status
        qrResult.textContent = `QR Code Data: ${code.data}`;
        updateStatus('green', 'QR Code Detected!');
        console.log('QR Code Data:', code.data); // Output QR Code data to the console

        // Clear the timeout if there is an existing one
        if (qrDataTimeout) {
            clearTimeout(qrDataTimeout);
        }

        // Set a timeout to clear the data after 5 seconds
        qrDataTimeout = setTimeout(() => {
            qrResult.textContent = "No QR Code detected.";
            updateStatus('red', 'No QR Code Detected');
            isQRCodeDetected = false; // Reset the flag after 5 seconds
        }, 3000); // 5 seconds
    } else if (!code && isQRCodeDetected) {
        // If no QR code is detected, reset the flag for the next detection
        requestAnimationFrame(scanQRCode);
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
