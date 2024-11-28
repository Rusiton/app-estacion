function validateEmail(email){
    const pattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    return pattern.test(email);
}



async function recover(form_data){

    const form = new URLSearchParams(Object.fromEntries(form_data));

    const options = {
        method: "PUT",
        body: form.toString()
    }

    const response = await fetch(`https://mattprofe.com.ar/alumno/11994/app-estacion/api/User/recovery`, options);
    return await response.json();
}



function handleResponse(response){
    switch (response.errno) {
        case 200:
            modal_container.classList.add("visible");
            break;

        case 404:
            error_message.innerHTML = "Ese correo no se encuentra registrado";
            error_container.classList.add("visible");
            submit_button.removeAttribute("disabled");
            break;
    }
}



recovery_form.addEventListener("submit", async e => {
    e.preventDefault();

    submit_button.setAttribute("disabled", "");
    error_container.classList.remove("visible");

    if(!validateEmail(input_email.value)){
        submit_button.removeAttribute("disabled");
        error_container.classList.add("visible");
        error_message.textContent = "Correo inválido, por favor, revise cualquier mal escrito en la escritura.";
        return;
    }

    const form_data = new FormData(e.target);
    const response = await recover(form_data);
    handleResponse(response);
});