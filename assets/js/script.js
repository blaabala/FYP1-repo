function updateDateTime() {
    const now = new Date();
    const options = { year: 'numeric', month: 'numeric', day: 'numeric', 
                      hour: 'numeric', minute: 'numeric', second: 'numeric' };
    const formattedDateTime = now.toLocaleString('en-GB', { timeZone: 'Asia/Kuala_Lumpur', ...options });
    document.getElementById('datetime').innerHTML = formattedDateTime;
}


updateDateTime();

setInterval(updateDateTime, 1000);


let input_element = document.querySelector("input");

input_element.addEventListener("keyup", () => {
    input_element.setAttribute("value", input_element.value);
})

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