<?php
    require_once 'cuenta.php';

    class cuentaAlta extends cuenta{
        public $denominacion;
        public $cuit;

        function __construct($cbu, $denominacion, $cuit){
            parent::__construct($cbu);
            $this->denominacion = $this->verificarDenominacion($denominacion);
            $this->cuit = $this->verificarCuit(strval($cuit));
        }

        private function verificarDenominacion($numero){
            if(strlen($numero) >29){
                return substr($numero, 0, 29);
            }
            else{
                return $numero;
            }
        }
        private function verificarCuit($numero){
            $numeroLimpio = str_replace("-","", $numero);
            if(ctype_digit($numero)){
                return $numero;
            }
            else{
                throw new Exception('Error Cuit');
            }
        }

        public function generarLineaCuenta(){
            $linea = '2' . str_repeat(' ', 22) . $this->denominacion . str_repeat(' ', 29- strlen($this->denominacion)) .  'SSN' . $this->cuit . $this->cbu . str_repeat(' ', 72);
            return $linea;
        }
    }
?>