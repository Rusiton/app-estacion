function checkPasswords(first_password, second_password){
    if(first_password !== second_password){
        return false;
    }

    return true;
}



async function reset(form_data){
    const form = new URLSearchParams(Object.fromEntries(form_data));

    const options = {
        method: "PUT",
        body: form.toString()
    }

    const response = await fetch(`https://mattprofe.com.ar/alumno/11994/app-estacion/api/User/reset/`, options);
    return await response.json();
}

function handleResponseError(errno){
    switch (errno) {
        case 200:
            modal_container.classList.add("visible");

            setInterval(() => {
                if(parseInt(modal_countdown.textContent) === 0){
                    window.location = "login";
                    return;
                }
                modal_countdown.textContent = parseInt(modal_countdown.textContent) - 1;
            }, 1000);
            
            break;

        case 404:
            error_message.innerHTML = "Ese correo no se encuentra registrado";
            error_container.classList.add("visible");
            submit_button.removeAttribute("disabled");
            break;
    }
}



reset_form.addEventListener("submit", async e => {
    e.preventDefault();

    submit_button.setAttribute("disabled", "");
    error_container.classList.remove("visible");

    if(!checkPasswords(input_password.value, input_password_confirm.value)){
        submit_button.removeAttribute("disabled");
        error_container.classList.add("visible");
        error_message.textContent = "Las contraseñas no coinciden";
        return;
    }

    const token_action = window.location.search.replace("?", "");

    const form_data = new FormData(e.target);
    form_data.append("token_action", token_action);

    const response = await reset(form_data);
    console.log(response);

    handleResponseError(response.errno);
});