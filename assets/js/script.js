function updateDateTime() {
    const now = new Date();
    const options = { year: 'numeric', month: 'numeric', day: 'numeric', 
                      hour: 'numeric', minute: 'numeric', second: 'numeric' };
    const formattedDateTime = now.toLocaleString('en-GB', { timeZone: 'Asia/Kuala_Lumpur', ...options });
    document.getElementById('datetime').innerHTML = formattedDateTime;
}


updateDateTime();

setInterval(updateDateTime, 2000);


let input_element = document.querySelector("input");

input_element.addEventListener("keyup", () => {
    input_element.setAttribute("value", input_element.value);
})