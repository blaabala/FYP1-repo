document.addEventListener('DOMContentLoaded', function() {
    function updateDateTime() {
        const dateTimeDisplay = document.getElementById('current-datetime');
        if (dateTimeDisplay) {
            dateTimeDisplay.innerHTML = new Date().toLocaleString('en-MY', { timeZone: 'Asia/Kuala_Lumpur' });
        } else {
            console.warn('Element with ID "current-datetime" not found. Retrying in 1 second...');
            setTimeout(updateDateTime, 1000);
        }
    }
    updateDateTime();
    setInterval(updateDateTime, 1000);
});