<?php 

	// Desactiva el reporte de errores de mysqli
	mysqli_report(MYSQLI_REPORT_OFF);
	
	/**
	 * 
	 * Clase para la conexión con la base de datos
	 * 
	 * */
	class DBAbstract{

		public $db;

		/**
		 * 
		 * Conecta contra la base de datos usando las credenciales
		 * 
		 * */
		function __construct(){
			
			$this->conectar();

		}

		/**
		 * 
		 * Conecta con la base de datos
		 * 
		 * */
		function conectar(){
			$this->db = @new mysqli($_ENV['HOST'], $_ENV['USER'], $_ENV['PASS'], $_ENV['DB'], $_ENV['PORT']);	
		
			if($this->db->connect_errno){
				echo "Hubo un error en la conexión: (".$this->db->connect_errno.") ".$this->db->connect_error;

				exit();
			}
		}

		/**
		 * 
		 * realiza una consulta a la base de datos tipo DML
		 * 
		 * @param string $sql consulta en formato SQL
		 * @return array|bool lista indexada de forma asociativa (SELECT)|true (INSERT,UPDATE,DELETE)
		 * 
		 * */
		function query($sql){

			$this->conectar();
			
			$response = $this->db->query($sql);

			if($this->db->errno){
				echo "Error de consulta: ".$this->db->error;
				exit();
			}
			
			return $response;
		}

	}



 ?>