<?php
    class cuenta{
        public $cbu;
        function __construct($cbu){
            $this->cbu = $this->verificarCbu($cbu);
        }

        private function verificarCbu($numero){
            $numeroLimpio = str_replace(" ","", $numero);
            if(ctype_digit($numeroLimpio) && strlen($numeroLimpio) == 22){
                return $numeroLimpio;
            }
            else{
                throw new Exception('Error Cbu');
            }
        }
    }
?>