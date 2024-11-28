function validateEmail(email){
    const pattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    return pattern.test(email);
}



function checkPasswords(first_password, second_password){
    if(first_password !== second_password){
        return false;
    }

    return true;
}



async function register(form_data){
    const options = {
        method: "POST",
        body: form_data
    }

    const response = await fetch(`https://mattprofe.com.ar/alumno/11994/app-estacion/api/User/register`, options);
    return await response.json();
}



function handleResponseError(errno){
    switch (errno) {
        case 200:
            modal_container.classList.add("visible");
            break;

        case 402:
            error_message.innerHTML = "Este correo ya se encuentra en uso, por favor, ingrese otro o <a href='login'>inicie sesión</a>"
            error_container.classList.add("visible");
            submit_button.removeAttribute("disabled");
            break;
    }
}



register_form.addEventListener('submit', async e => {
    e.preventDefault();

    submit_button.setAttribute("disabled", "");
    error_container.classList.remove("visible");

    if(!validateEmail(input_email.value)){
        submit_button.removeAttribute("disabled");
        error_container.classList.add("visible");
        error_message.textContent = "Correo inválido, por favor, revise cualquier mal escrito en la escritura";
        return;
    }

    if(!checkPasswords(input_password.value, input_password_confirm.value)){
        submit_button.removeAttribute("disabled");
        error_container.classList.add("visible");
        error_message.textContent = "Las contraseñas no coinciden";
        return;
    }

    const form_data = new FormData(e.target);
    const response = await register(form_data);
    handleResponseError(response.errno);
});