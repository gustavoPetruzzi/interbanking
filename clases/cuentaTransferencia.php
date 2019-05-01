<?php
    class cuentaTransferencia extends cuenta{
        public $importe;
        //public $coma;
        
        function __construct($cbu, $importe){
            parent::__construct($cbu);
            $this->importe = $this->verificarImporte($importe, $coma);
            
        }

        private function verificarImporte($importe, $coma){
            
            $importeTransformado = str_replace("$", "", $importe);
            
            $importeTransformado = preg_replace(
                "/(\t|\n|\v|\f|\r| |\xC2\x85|\xc2\xa0|\xe1\xa0\x8e|\xe2\x80[\x80-\x8D]|\xe2\x80\xa8|\xe2\x80\xa9|\xe2\x80\xaF|\xe2\x81\x9f|\xe2\x81\xa0|\xe3\x80\x80|\xef\xbb\xbf)+/",
                "", $importeTransformado);
            return $importeTransformado;
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