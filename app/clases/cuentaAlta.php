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

        public static function generarArchivo($cuentas, $numeroCliente){
            $fecha = new DateTime();
            $encabezado = str_pad( ("1".$numeroCliente), 160);
            $nombre = "cuentas-". $fecha->getTimestamp(). ".txt";
            $gestor = fopen($nombre, "w");
            fwrite($gestor, $encabezado);
            fwrite($gestor, "\r\n");
            foreach ($cuentas as $cuenta) {
                fwrite($gestor, $cuenta->generarLineaCuenta());
                fwrite($gestor, "\r\n");
            }
            //fwrite($gestor, $cuentas);
            if(count($cuentas) > 99){
                $finalCliente = ("3" . $numeroCliente) . "000" . count($cuentas);
                $final = str_pad($finalCliente, 160);
                fwrite($gestor, $final);
            }
            else if(count($cuentas) > 9){
                $finalCliente = ("3" . $numeroCliente). "0000" . count($cuentas);
                $final = str_pad($finalCliente, 160);
                fwrite($gestor, $final);
            }
            else if(count($cuentas) > 0){
                $finalCliente = ("3" . $numeroCliente) . "00000" . count($cuentas);
                $final = str_pad($finalCliente, 160);
                fwrite($gestor, $final);
            }
            fclose($gestor);
            return $nombre;
        }
    }
?>