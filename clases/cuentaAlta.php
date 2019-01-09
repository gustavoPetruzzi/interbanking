<?php
    require_once 'cuenta.php';

    class cuentaAlta extends cuenta{
        public $denominacion;
        public $cuit;
        public $tipos;
        function __construct($cbu, $denominacion, $cuit){
            parent::__construct($cbu);
            $this->denominacion = $this->verificarDenominacion($denominacion);
            $this->cuit = $this->verificarCuit($cuit);
            //$this->tipo = $this->verificarTipo($tipo);
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
            $numeroLimpio = str_replace(" ","", $numeroLimpio);
            if(ctype_digit($numeroLimpio)){
                return $numeroLimpio;
            }
            else{
                throw new Exception('Error Cuit');
            }
            
        }
        /*
        private function verificarTipo($tipo){
            $tipoLimpio = str_replace(" ","", $tipo);
            if(ctype_alpha($tipoLimpio) && strlen($tipoLimpio) == 3){
                return $numero;
            }
            else{
                throw new Exception('Error tipo');
            }

        }
        */

        public function generarLineaCuenta(){
            $linea = '2' . str_repeat(' ', 22) . $this->denominacion . str_repeat(' ', 29- strlen($this->denominacion)) .  'SSN' . $this->cuit . $this->cbu . str_repeat(' ', 72);
            return $linea;
        }
    }
?>