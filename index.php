<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require 'vendor/autoload.php';
require 'clases/cuentaAlta.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
$config['displayErrorDetails'] = true;
$app = new \Slim\App;
$app->add(function($request, $response, $next){
    $response = $next($request, $response);
    return $response
         ->withHeader('Access-Control-Allow-Origin', '*')
         ->withHeader('Access-Control-Allow-Headers', 'X-API-KEY,Access-Control-Request-Method,X-Requested-With, Content-Type, Accept, Origin, Authorization')
         ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
 });

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
    //$encabezado = "1C16884A" . str_repeat($encodeado, 76);
    $encabezado = str_pad("1C16884A", 160);
    $gestor = fopen("cuentas-". $fecha->getTimestamp(). ".txt", "w");
    fwrite($gestor, $encabezado);
    fwrite($gestor, "\r\n");
    for ($fila= 1; $fila < $maxFila +1; $fila++) { 
        //columna 1 cbu, 2 denominacion, 3 cuil 
        try{
            $denominacion = $hoja->getCellByColumnAndRow(2, $fila)->getValue();
            $cuil = $hoja->getCellByColumnAndRow(3, $fila)->getValue();
            $cbu = preg_replace("/[^0-9]/", "", $hoja->getCellByColumnAndRow(1, $fila)->getFormattedValue());
            //$cbu = $hoja->getCellByColumnAndRow(1, $fila)->getValue();
            
            $cuenta = new cuentaAlta($cbu, $denominacion, $cuil);
            array_push($arrayCuentas, $cuenta);
            $algo = "ó";
            //array_push($arrayErrores, $cuil);
            //array_push($arrayCuentas, $denominacion);
            //array_push($arrayCuentas, $cbu);
            //array_push($arrayCuentas, $cuenta->cuit);
            fwrite($gestor, $cuenta->generarLineaCuenta());
            fwrite($gestor, "\r\n");

        }
        catch(Exception $e){
            $linea = array($cbu, $denominacion, $cuil, $e->getMessage());
            array_push($arrayErrores, $linea);
        }
    }
    
    //fwrite($gestor, "3C16884A" . str_repeat("0", 6- count($arrayCuentas)) . count($arrayCuentas) . str_repeat($encodeado, 73));
    if(count($arrayCuentas) > 99){
        $finalCliente = "3C16884A" . "000" . count($arrayCuentas);
        $final = str_pad($finalCliente, 160);
        fwrite($gestor, $final);
    }
    else if(count($arrayCuentas) > 9){
        $finalCliente = "3C16884A" . "0000" . count($arrayCuentas);
        $final = str_pad($finalCliente, 160);
        fwrite($gestor, $final);
    }
    fclose($gestor);
    $retorno['cuentas'] = $arrayCuentas;
    $retorno['errores'] = $arrayErrores;
    $retorno['link']= "algo";
    return $response->withJson($retorno);
    
    //return $response->withJson($_FILES);
});

$app->post('/generarTransferenciasSueldos', function (Request $request, Response $response, array $args){
    $arrayCuentas = array();
    $arrayErrores = array();
    $nombreArchivo = $_FILES['archivo']['tmp_name'];
    $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
    $archivo = $reader->load($nombreArchivo);
    $hoja = $archivo->getActiveSheet();
    $maxFila = $hoja->getHighestRow();
    $fecha = new DateTime();

    $gestor = fopen("transferencias-" . $fecha->getTimestamp() . ".txt", "w");
    $encabezado = "*U*1111111111211111111112D";
});
$app->run();

