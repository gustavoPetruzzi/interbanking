<?php
    class cuentaTransferencia extends cuenta{
        public $importe;
        
        function __construct($cbu, $importe){
            parent::__construct($cbu);
            //$importe = $this->verificarImporte($importe);
            $this->importe = $importe;
        }

        private function verificarImporte($importe){
            $importeTransformado = str_replace("$","",$importe);
            $importeTransformado = str_replace(" ","", $importe);
            return str_replace(",", "", $importeTransformado);
        }

        private function importeLinea(){
            return ($this->importe * 100);
        }

        public function generarLineaTransferenciaSueldos(){
            $importeFormateado = $this->importe * 100;
            return $linea = "*M*" . $this->cbu . str_repeat('0', 17-strlen($importeFormateado)) . $importeFormateado . str_repeat(' ', 59) . '00' . str_repeat(' ', 136);
            
        }

        public function generarLineaTransferenciaProveedores(){
            $importeFormateado = $this->importe *100;
            return $linea = "*M*" . $this->cbu . str_repeat('0', 17-strlen($importeFormateado)) . $importeFormateado . str_repeat(' ', 60) . 'FA00000000001' . str_repeat(' ', 29) . '000000000000' . str_repeat(' ', 12) . '0000000000' . str_repeat(' ', 61); 
        }
    }
?>