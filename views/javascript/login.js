login_form.addEventListener('submit', async e => {
    e.preventDefault();

    submit_button.setAttribute("disabled", "");
    error_container.classList.remove("visible");

    const form_data = new FormData(e.target);

    const email = form_data.get("email");
    const password = form_data.get("password");

    const response = await login(email, password);
    handleResponseError(response.errno);
});



async function login(email, password){
    const response = await fetch(`https://mattprofe.com.ar/alumno/11994/app-estacion/api/User/login/?email=${email}&password=${password}`);
    return await response.json();
}



function handleResponseError(errno){
    switch (errno) {
        case 200:
            window.location = "panel";
            break;

        case 404:
            error_message.textContent = "Credenciales no válidas"
            error_container.classList.add("visible");
            submit_button.removeAttribute("disabled");
            break;

        case 405:
            error_message.textContent = "Su usuario aún no se ha validado, revise su casilla de correo"
            error_container.classList.add("visible");
            submit_button.removeAttribute("disabled");
            break;
    
        case 406:
            error_message.textContent = "Su usuario está bloqueado, revise su casilla de correo"
            error_container.classList.add("visible");
            submit_button.removeAttribute("disabled");
            break;

        case 407:
            error_message.textContent = "Su usuario está bloqueado, revise su casilla de correo"
            error_container.classList.add("visible");
            submit_button.removeAttribute("disabled");
            break;

        case 408:
            error_message.textContent = "Contraseña inválida"
            error_container.classList.add("visible");
        submit_button.removeAttribute("disabled");
            break;
    }
}