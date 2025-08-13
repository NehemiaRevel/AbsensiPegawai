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
// Access the camera
        const video = document.getElementById('video');

// Check if the browser supports media devices
if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
    // Request access to the camera
    navigator.mediaDevices.getUserMedia({ video: true })
        .then(function(stream) {
            // Set the video source to the camera stream
            video.srcObject = stream;
        })
        .catch(function(error) {
            console.error("Error accessing the camera: ", error);
        });
} else {
    alert("Your browser does not support accessing the camera.");
}

 // Function to stop the camera
function stopCamera() {
    if (stream) {
        const tracks = stream.getTracks();
        tracks.forEach(track => track.stop());
        video.srcObject = null;
        toggleButton.textContent = 'Turn On Camera';
    }
}

// Toggle camera on/off
toggleButton.addEventListener('click', function() {
    if (toggleButton.textContent === 'Turn On Camera') {
        startCamera();
    } else {
        stopCamera();
    }
});