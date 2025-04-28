document.addEventListener('DOMContentLoaded', function() {
    function updateDateTime() {
        const dateTimeDisplay = document.getElementById('current-datetime');
        if (dateTimeDisplay) {
            dateTimeDisplay.innerHTML = new Date().toLocaleString('en-MY', { timeZone: 'Asia/Kuala_Lumpur' });
        } else {
            console.error('Element with ID "current-datetime" not found.');
        }
    }
    updateDateTime();
    setInterval(updateDateTime, 1000);
});