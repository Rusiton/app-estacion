<?php

    include_once 'DBAbstract.php'; // incluye la libreria para conectar con la db
    include_once 'Mailer.php'; // Se incluye la clase para el envio de correo electrónico

    class User extends DBAbstract{

        public $attributes = array();
        public $user_ip = NULL;
        public $user_so = NULL;
        public $user_browser = NULL;

        /**
         * 
         * Constructor de la clase, ejecuta el constructor de DBAbstract
         * 
         * */
        function __construct(){
            parent::__construct();

            $result = $this->query('DESCRIBE appEstacion__users');

            $excluded_fields = ["contraseña"];

            foreach ($result as $key => $value) {
                if(!in_array($value['Field'], $excluded_fields)){
                    $attrib = $value['Field']; // guarda el nombre de la columna en una variable

                    $this->attributes[$value['Field']] = ""; // Guarda los nombres de las columnas en un vector
                }
            }

            $this->user_ip = $_SERVER["REMOTE_ADDR"];
            $this->user_so = $_SERVER["HTTP_USER_AGENT"];

            if(isset($_SERVER["HTTP_SEC_CH_UA"])){
                $this->user_browser = $_SERVER["HTTP_SEC_CH_UA"];
            }
            else{
                $this->user_browser = "Unknown";
            }
            
        }



        private function runSendEmailScript($email_form){

            $email = $email_form["destinatario"];
            $email_purpose = str_replace(" ", "_-_", $email_form["motivo"]);
            $email_content = str_replace(" ", "_-_", $email_form["contenido"]);

            var_dump(str_replace("", "_-_", $email_content));

            $command = "php " . __DIR__ . "/SendEmail.php $email $email_purpose $email_content";

            exec($command, $output);

            print_r($output);

        }



        function getUserAttributes(){
            return $this->attributes;
        }



        function setUserAttributes($attributes){
            foreach($attributes as $key => $value){
                $this->attributes[$key] = $value;
            }
        }



        function getEmailTemplate($template, $vars = NULL){
            $email_buffer = file_get_contents("https://mattprofe.com.ar/alumno/11994/app-estacion/views/templates/$template.html");

            if(isset($vars)){
                return $this->setEmailVariables($email_buffer, $vars);
            }

            return $email_buffer;
        }



        function setEmailVariables($email_buffer, $vars){
            foreach($vars as $key => $value){
                if($this->testEmailVariable($email_buffer, $key)){
                    $email_buffer = str_replace("{{". $key ."}}", $value, $email_buffer);
                }
                else{
					echo "La variable de plantilla <b>".$key."</b> no existe";
					exit();
				}
            }

            return $email_buffer;

        }



        function testEmailVariable($email_buffer, $variable){
            return strpos($email_buffer, $variable);
        }



        function getUserByTokenAction($params){
            if($_SERVER["REQUEST_METHOD"] !== "GET"){
                return ["errno" => 400, "error" => "Método HTTP incorrecto"];
            }

            if(!isset($params["token_action"]) || $params["token_action"] === ""){
                return ["errno" => 401, "error" => "Parámetro 'password' inexistente o vacío"];
            }

            $token_action = $params["token_action"];

            $response = $this->query("CALL user_get_by_token_action('$token_action')");
			$response = $response->fetch_all(MYSQLI_ASSOC);

            if(count($response) === 0){
                return ["errno" => 404, "error" => "Correo no encontrado"];
            }

            return ["errno" => 200, "error" => "Correo encontrado", "response" => $response];
        }



        function register($params){

            if($_SERVER["REQUEST_METHOD"] !== "POST"){
                return ["errno" => 400, "error" => "Método HTTP incorrecto"];
            }

            if(!isset($params["email"]) || $params["email"] === ""){
                return ["errno" => 401, "error" => "Parámetro 'email' inexistente o vacío"];
            }

            if(!isset($params["password"]) || $params["password"] === ""){
                return ["errno" => 401, "error" => "Parámetro 'password' inexistente o vacío"];
            }

            $email = $params["email"];
            $password = md5($params["password"]);

            $response = $this->query("SELECT * FROM appEstacion__users WHERE email = '$email'");
			$response = $response->fetch_all(MYSQLI_ASSOC);

			if(count($response) > 0){ // Si se encontró un registro
                if($response[0]["delete_date"] === NULL){ // Si el usuario no está eliminado
                    return ["errno" => 402, "error" => "Usuario ya registrado"];
                }
            }

            $user_token = md5($_ENV["PROJECT_TOKEN"] . date("YmdHis" . mt_rand(0, 1000) . $email));
            $token_action = md5($_ENV["PROJECT_TOKEN"] . date("YmdHis" . mt_rand(0,1000)));

            $this->query("CALL user_register('$user_token', '$email', '$password', '$token_action')");

            $email_object = new Mailer();
            $email_purpose = "Validación de correo";
            $email_content = $this->getEmailTemplate("verificationEmail", ["TOKEN_ACTION" => $token_action]);
            
            $email_form = ["destinatario" => $email, "motivo" => $email_purpose, "contenido" => $email_content];
            $email_object->send($email_form);
            
            return ["errno" => 200, "error" => "Usuario registrado correctamente"];

        }



        function login($params){

            if($_SERVER["REQUEST_METHOD"] !== "GET"){
                return ["errno" => 400, "error" => "Método HTTP incorrecto"];
            }

            if(!isset($params["email"]) || $params["email"] === ""){
                return ["errno" => 401, "error" => "Parámetro 'email' inexistente o vacío"];
            }

            if(!isset($params["password"]) || $params["password"] === ""){
                return ["errno" => 401, "error" => "Parámetro 'password' inexistente o vacío"];
            }

            $email = $params["email"];
            $password = md5($params["password"]);

			$response = $this->query("CALL user_get_by_email('$email')");
			$response = $response->fetch_all(MYSQLI_ASSOC);

			if(count($response) === 0){
				return ["errno" => 404, "error" => "Usuario no encontrado"];
			}
                
            if($response[0]["activo"] === "0"){
                return ["errno" => 405, "error" => "Usuario inactivo"];
            }

            if($response[0]["bloqueado"] === "1"){
                return ["errno" => 406, "error" => "Usuario bloqueado"];
            }

            if($response[0]["recupero"] === "1"){
                return ["errno" => 407, "error" => "Usuario en proceso de recuperación"];
            }

            $token = $response[0]["token"];

            if($response[0]["contraseña"] !== $password){
                $email_object = new Mailer();
                $email_purpose = "Intento de acceso";
                $email_content = $this->getEmailTemplate("loginTryEmail", ["IP" => $this->user_ip, "SO" => $this->user_so, "BROWSER" => $this->user_browser, "TOKEN" => $token]);
                
                $email_form = ["destinatario" => $email, "motivo" => $email_purpose, "contenido" => $email_content];
                $email_object->send($email_form);
                
                return ["errno" => 408, "error" => "Contraseña invalida"];
            }

            unset($response[0]["contraseña"]);

            $email_object = new Mailer();
            $email_purpose = "Inicio de sesión";
            $email_content = $this->getEmailTemplate("loginEmail", ["IP" => $this->user_ip, "SO" => $this->user_so, "BROWSER" => $this->user_browser, "TOKEN" => $token]);

            $email_form = ["destinatario" => $email, "motivo" => $email_purpose, "contenido" => $email_content];
            $email_object->send($email_form);

            $this->setUserAttributes($response[0]);
            
            $_SESSION[$_ENV["PROJECT_TOKEN"]]['user'] = $this;

            return ["errno" => 200, "error" => "Sesión iniciada"];

        }



        function validate($params){

            if($_SERVER["REQUEST_METHOD"] !== "PUT"){
                return ["errno" => 400, "error" => "Método HTTP incorrecto"];
            }

            if(!isset($params["token_action"]) || $params["token_action"] === ""){
                return ["errno" => 401, "error" => "Parámetro 'token_action' inexistente o vacío"];
            }

            $token_action = $params["token_action"];

            $response = $this->query("CALL user_get_by_token_action('$token_action')");
			$response = $response->fetch_all(MYSQLI_ASSOC);

            if(count($response) === 0){
                return ["errno" => 404, "error" => "El token no corresponde a un usuario"];
            }

            $this->query("CALL user_validate('$token_action')");

            $email = $response[0]["email"];

            $email_object = new Mailer();
            $email_purpose = "Validación exitosa";
            $email_content = $this->getEmailTemplate("verifiedUserEmail");
            
            $email_form = ["destinatario" => $email, "motivo" => $email_purpose, "contenido" => $email_content];
            $email_object->send($email_form);

            return ["errno" => 200, "error" => "Usuario validado"];

        }



        function block($params){

            if($_SERVER["REQUEST_METHOD"] !== "PUT"){
                return ["errno" => 400, "error" => "Método HTTP incorrecto"];
            }

            if(!isset($params["token"]) || $params["token"] === ""){
                return ["errno" => 401, "error" => "Parámetro 'token' inexistente o vacío"];
            }

            $token = $params["token"];

            $response = $this->query("CALL user_get_by_token('$token')");
			$response = $response->fetch_all(MYSQLI_ASSOC);

            if(count($response) === 0){
                return ["errno" => 404, "error" => "El token no corresponde a un usuario"];
            }

            $token_action = md5($_ENV["PROJECT_TOKEN"] . date("YmdHis" . mt_rand(0, 1000)));

            $this->query("CALL user_block('$token', '$token_action')");

            $email = $response[0]["email"];

            $email_object = new Mailer();
            $email_purpose = "Cuenta bloqueada";
            $email_content = $this->getEmailTemplate("blockedEmail", ["TOKEN_ACTION" => $token_action]);
            
            $email_form = ["destinatario" => $email, "motivo" => $email_purpose, "contenido" => $email_content];
            $email_object->send($email_form);

            return ["errno" => 200, "error" => "Usuario bloqueado"];

        }



        function recovery($params){

            if($_SERVER["REQUEST_METHOD"] !== "PUT"){
                return ["errno" => 400, "error" => "Método HTTP incorrecto"];
            }

            if(!isset($params["email"]) || $params["email"] === ""){
                return ["errno" => 401, "error" => "Parámetro 'email' inexistente o vacío"];
            }

            $email = $params["email"];

            $response = $this->query("CALL user_get_by_email('$email')");
			$response = $response->fetch_all(MYSQLI_ASSOC);

            if(count($response) === 0){
                return ["errno" => 404, "error" => "Correo no encontrado"];
            }

            $token_action = md5($_ENV["PROJECT_TOKEN"] . date("YmdHis" . mt_rand(0, 1000)));

            $this->query("CALL user_start_recover('$email', '$token_action')");

            $email_object = new Mailer();
            $email_purpose = "Reestablecer contraseña";
            $email_content = $this->getEmailTemplate("recoverEmail", ["TOKEN_ACTION" => $token_action]);
            
            $email_form = ["destinatario" => $email, "motivo" => $email_purpose, "contenido" => $email_content];
            $email_object->send($email_form);

            return ["errno" => 200, "error" => "Proceso de reestablecimiento de contraseña iniciado", "token_action" => $token_action];
            
        }



        function reset($params){

            if($_SERVER["REQUEST_METHOD"] !== "PUT"){
                return ["errno" => 400, "error" => "Método HTTP incorrecto"];
            }

            if(!isset($params["password"]) || $params["password"] === ""){
                return ["errno" => 401, "error" => "Parámetro 'password' inexistente o vacío"];
            }

            if(!isset($params["token_action"]) || $params["token_action"] === ""){
                return ["errno" => 401, "error" => "Parámetro 'token_action' inexistente o vacío"];
            }

            $password = md5($params["password"]);
            $token_action = $params["token_action"];

            $response = $this->query("CALL user_get_by_token_action('$token_action')");
			$response = $response->fetch_all(MYSQLI_ASSOC);

            if(count($response) === 0){
                return ["errno" => 404, "error" => "Correo no encontrado"];
            }

            $this->query("CALL user_reset_password('$password', '$token_action')");

            $token = $response[0]["token"];
            $email = $response[0]["email"];

            $email_object = new Mailer();
            $email_purpose = "Reestablecimiento de contraseña";
            $email_content = $this->getEmailTemplate("passResetEmail", ["IP" => $this->user_ip, "SO" => $this->user_so, "BROWSER" => $this->user_browser, "TOKEN" => $token]);
            
            $email_form = ["destinatario" => $email, "motivo" => $email_purpose, "contenido" => $email_content];
            $email_object->send($email_form);

            return ["errno" => 200, "error" => "Se ha reestablecido la contraseña"];

        }

    }

?>