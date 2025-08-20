// --- Date Display ---
const now = new Date();
// Get the hours and minutes, ensuring they are two digits
const hours = now.getHours().toString().padStart(2, '0');
const minutes = now.getMinutes().toString().padStart(2, '0');

// Format the time in HH:MM (24-hour format)
const timeString = `${hours}:${minutes}`;
// Format the date into local format (Indonesian)
const dateString = now.toLocaleDateString('id-ID', {
    day: '2-digit',
    month: 'long',
    year: 'numeric'
});

// Combine the date and time
const dateTimeString = `${dateString}, ${timeString}`;
// Display the formatted date and time
document.getElementById("todayDate").textContent = dateTimeString;

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