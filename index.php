<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require 'vendor/autoload.php';
require 'clases/cuentaAlta.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
$app = new \Slim\App;

$app->get('/', function (Request $request, Response $response, array $args) {
    
    $response->getBody()->write("Hello, asdasd");

    return $response;
});
$app->post('/generarAltaCuenta', function (Request $request, Response $response, array $args) {
    $arrayCuentas = array();
    $arrayErrores = array();
    $nombreArchivo = $_FILES['archivo']['tmp_name'];
    $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
    $archivo = $reader->load($nombreArchivo);
    $hoja = $archivo->getActiveSheet();
    $maxFila = $hoja->getHighestRow();
    $fecha = new DateTime();
    $encodeado = mb_convert_encoding(" ", "UTF-16LE");
    $encabezado = "1C16884A" . str_repeat($encodeado, 76);
    
    $gestor = fopen("cuentas-". $fecha->getTimestamp(). ".txt", "w");
    fwrite($gestor, $encabezado);
    fwrite($gestor, "\r\n");
    for ($fila= 1; $fila < $maxFila; $fila++) { 
        //columna 1 cbu, 2 denominacion, 3 cuil
        try{
            $denominacion = $hoja->getCellByColumnAndRow(2, $fila)->getValue();
            $cuil = $hoja->getCellByColumnAndRow(3, $fila)->getValue();
            $cbu = preg_replace("/[^0-9]/", "", $hoja->getCellByColumnAndRow(1, $fila)->getValue());
            
            $cuenta = new cuentaAlta($cbu, $denominacion, $cuil);

            array_push($arrayCuentas, $cuenta);
            fwrite($gestor, $cuenta->generarLineaCuenta());
            fwrite($gestor, "\r\n");

        }
        catch(Exception $e){
            array_push($arrayErrores, $e);
        }
    }
    
    fwrite($gestor, "3C16884A" . str_repeat("0", 6- count($arrayCuentas)) . count($arrayCuentas) . str_repeat($encodeado, 73));
    
    fclose($gestor);
    $retorno['cuentas'] = $arrayCuentas;
    $retorno['errores'] = $arrayErrores;
    return $response->withJson($retorno);
    
});
$app->run();

