<?php

    class TPLEngine{

        public $buffer;
		private $vista;
		private $error;
		private $errno;

        /**
		 * 
		 * Carga la plantilla o vista en memoria
		 * @param string $name nombre de la vista
		 * 
		 * */
		function __construct($name){
			
			/*< guarda el nombre de la vista*/
			$this->vista = $name;
			
			/*< valida que exista la vista*/
			if(!file_exists("views/".$this->vista."View.html")){
				echo "Fallo al cargar la vista <b>".$this->vista."</b> El archivo no exista";
				
				$this->error = "No se encontro la vista ".$this->vista;
				$this->errno = 404;

			}

			/*< carga la vista en buffer*/
			$this->buffer = file_get_contents("views/".$this->vista."View.html");

			$this->error = "";
			$this->errno = 200;

			/*< se carga los archivos externos*/
			$this->load_extern();

		}



		/**
		 * 
		 * Reemplaza las variables dentro de la plantilla
		 * @param array $vars es un arreglo indexado de forma asociativa, el index es la variable
		 * 
		 * */
		function set_vars($vars){
			foreach ($vars as $key => $value) {
				if($this->test_var($key)){
					$this->buffer = str_replace("{{".$key."}}", $value, $this->buffer);
				}else{
					echo "La variable de plantilla <b>".$key."</b> no existe";
					exit();
				}
			}

		}



		/**
		 * 
		 * Verifica si existe la variable
		 * @param string $name nombre de la variable
		 * @return bool existe| no existe la variable
		 * 
		 * */
		private function test_var($name){
			return strpos($this->buffer, $name);
		}



		/**
		 * 
		 * Busca los @extern('z') y los reemplaza por el contenido del archivo con el nombre encerrado entre comillas
		 * @brief busca y reemplaza los @extern con el archivo correspondiente 
		 * 
		 * */
		private function load_extern(){

			// REGEX para buscar el patron de un extern
			$pattern = "/@extern\(['\"]([a-zA-Z0-9_]+)['\"]\)/";

			/*< busca todos las coincidencias con el patrón*/
			preg_match_all($pattern, $this->buffer, $externs);

			/*< recorre todas las coincidencias*/
			foreach ($externs[0] as $key => $extern) {
				
				/*< nos quedamos con el nombre encerrado entre comillas*/
				$extern_view = $externs[1][$key];

				/*< valida que exista el archivo externo*/
				if(!file_exists("views/".$extern_view.".html")){
					echo "Archivo de extern no encontrado. <b>".$extern_view."</b>";
					exit();
				}

				/*< carga en memoria el contenido del archivo externo*/
				$extern_buffer = file_get_contents("views/".$extern_view.".html");
		
				/*< reemplaza en la vista el @extern encontrado con el contenido del archivo*/
				$this->buffer = str_replace($extern, $extern_buffer, $this->buffer);
			}

		}



		/**
		 * 
		 * Imprime la plantilla o vista en la página
		 * @return bool verdadero si imprimio
		 * 
		 * */
		function print_view(){
			echo $this->buffer;
			return true;
		}

    }

?>