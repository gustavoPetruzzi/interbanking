<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require 'vendor/autoload.php';
require 'clases/cuentaAlta.php';
require 'clases/cuentaTransferencia.php';
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



$app->post('/generarAltaCuenta', function (Request $request, Response $response, array $args) {
    
    $arrayCuentas = array();
    $arrayErrores = array();
    $nombreArchivo = $_FILES['archivo']['tmp_name'];
    $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
    $archivo = $reader->load($nombreArchivo);
    $hoja = $archivo->getActiveSheet();
    $maxFila = $hoja->getHighestRow();
    $fecha = new DateTime();
    $numeroCliente = "";
    $numeroCliente = $_POST['cliente'];

    $encabezado = str_pad( ("1".$numeroCliente), 160);
    $nombre = "cuentas-". $fecha->getTimestamp(). ".txt";
    $gestor = fopen($nombre, "w");
    fwrite($gestor, $encabezado);
    fwrite($gestor, "\r\n");
    for ($fila= 1; $fila < $maxFila +1; $fila++) { 
        //columna 1 cbu, 2 denominacion, 3 cuil 
        try{
            $denominacion = $hoja->getCellByColumnAndRow(2, $fila)->getValue();
            $cuil = $hoja->getCellByColumnAndRow(3, $fila)->getValue();
            $cbu = preg_replace("/[^0-9]/", "", $hoja->getCellByColumnAndRow(1, $fila)->getFormattedValue());
            
            $cuenta = new cuentaAlta($cbu, $denominacion, $cuil);
            array_push($arrayCuentas, $cuenta);
            fwrite($gestor, $cuenta->generarLineaCuenta());
            fwrite($gestor, "\r\n");

        }
        catch(Exception $e){
            $linea = array($cbu, $denominacion, $cuil, $e->getMessage());
            array_push($arrayErrores, $linea);
        }
    }
    
    
    if(count($arrayCuentas) > 99){
        $finalCliente = ("3" . $numeroCliente) . "000" . count($arrayCuentas);
        $final = str_pad($finalCliente, 160);
        fwrite($gestor, $final);
    }
    else if(count($arrayCuentas) > 9){
        $finalCliente = ("3" . $numeroCliente). "0000" . count($arrayCuentas);
        $final = str_pad($finalCliente, 160);
        fwrite($gestor, $final);
    }
    else if(count($arrayCuentas) > 0){
        $finalCliente = ("3" . $numeroCliente) . "00000" . count($arrayCuentas);
        $final = str_pad($finalCliente, 160);
        fwrite($gestor, $final);
    }
    fclose($gestor);
    $retorno['cuentas'] = $arrayCuentas;
    $retorno['errores'] = $arrayErrores;
    $retorno['link']= $nombre;
    return $response->withJson($retorno);
    
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
    $cbuPropio = $_POST['cbuPropio'];
    
    
    $encabezado = "*U*". $cbuPropio."D".date('Ymd')."SOBSER".str_repeat(" ", 64 - strlen("SOBSER")) .date("Ymd");
    $encabezado = $encabezado . str_repeat(" ", 133);
    $nombre = "transferencias-".$fecha->getTimestamp() . ".txt";
    $gestor = fopen($nombre, "w");
    fwrite($gestor, $encabezado);
    fwrite($gestor, "\r\n");
    for($fila = 2; $fila < $maxFila+1; $fila++){
        try{
            $cbu = preg_replace("/[^0-9]/", "", $hoja->getCellByColumnAndRow(1, $fila)->getFormattedValue());
            
            $importe = $hoja->getCellByColumnAndRow(2, $fila)->getValue();

            if(is_float($importe)){
                $importe = number_format($importe, 2);    
            }
            else{
                throw new Exception("ERROR IMPORTE");    
            }
            $importeTransformado = str_replace(",","",$importe);      
            $cuenta = new cuentaTransferencia($cbu, $importeTransformado);
            array_push($arrayCuentas, $cuenta);
            fwrite($gestor, $cuenta->generarLineaTransferenciaSueldos());
            fwrite($gestor, "\r\n");

        }
        catch(Exception $e){
            $linea = array($cbu,$importe, $fila, $e->getMessage());
            array_push($arrayErrores, $linea);
        }


    }
    
    fclose($gestor);
    $retorno['cuentas'] = $arrayCuentas;
    $retorno['errores'] = $arrayErrores;
    $retorno['link']= $nombre;
    return $response->withJson($retorno);    

});
$app->post('/v2/generarTransferenciasSueldos', function (Request $request, Response $response, array $args){
    $arrayCuentas = array();
    $arrayErrores = array();
    $nombreArchivo = $_FILES['archivo']['tmp_name'];
    $extension = pathinfo($_FILES['archivo']['name'], PATHINFO_EXTENSION);
    $extension  =strtolower($extension);
    switch ($extension) {
        case 'xlsx':
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            break;
        case 'xls':
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
            break;
        case 'ods':
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Ods();
            break;
        default:
            return $response->withJson('Formato de archivo no soportado', 400);
            break;
    }
    $archivo = $reader->load($nombreArchivo);
    $hoja = $archivo->getActiveSheet();
    $maxFila = $hoja->getHighestRow();
    $fecha = new DateTime();
    $cbuPropio = $_POST['cbuPropio'];
    $consolidado = $_POST['consolidado'];
    $encabezado = "*U*". $cbuPropio."D".date('Ymd').$consolidado."OBSER".str_repeat(" ", 64 - strlen("SOBSER")) .date("Ymd");
    $encabezado = $encabezado . str_repeat(" ", 133);
    $nombre = "transferencias-".$fecha->getTimestamp() . ".txt";
    $gestor = fopen($nombre, "w");
    fwrite($gestor, $encabezado);
    fwrite($gestor, "\r\n");
    
    for($fila = 2; $fila < $maxFila+1; $fila++){
        try{
            $cbu = preg_replace("/[^0-9]/", "", $hoja->getCellByColumnAndRow(1, $fila)->getFormattedValue());
            
            $importe = $hoja->getCellByColumnAndRow(2, $fila)->getValue();

            if(is_float($importe)){
                $importe = number_format($importe, 2);    
            }
            else{
                throw new Exception("ERROR IMPORTE");    
            }
            $observaciones = $hoja->getCellByColumnAndRow(3, $fila)->getValue();
            $importeTransformado = str_replace(",","",$importe);      
            $cuenta = cuentaTransferencia::conObservaciones($cbu,$importeTransformado, $observaciones);
            
            //$cuenta = new cuentaTransferencia($cbu, $importeTransformado);
            array_push($arrayCuentas, $cuenta);
            fwrite($gestor, $cuenta->generarLineaTransferenciaSueldos());
            fwrite($gestor, "\r\n");

        }
        catch(Exception $e){
            $linea = array($cbu,$importe, $fila, $e->getMessage());
            array_push($arrayErrores, $linea);
        }
    }
    
    fclose($gestor);
    $retorno['cuentas'] = $arrayCuentas;
    $retorno['errores'] = $arrayErrores;
    $retorno['link']= $nombre;
    return $response->withJson($retorno);

    
});

$app->post('/generarTransferenciasProveedores', function (Request $request, Response $response, array $args){
    $arrayCuentas = array();
    $arrayErrores = array();
    $nombreArchivo = $_FILES['archivo']['tmp_name'];
    $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
    $archivo = $reader->load($nombreArchivo);
    $hoja = $archivo->getActiveSheet();
    $maxFila = $hoja->getHighestRow();
    $fecha = new DateTime();
    $cbuPropio = $_POST['cbuPropio'];

    
    $encabezado = "*U*". $cbuPropio."D".date('Ymd')."SOBSER".str_repeat(" ", 64 - strlen("SOBSER")) .date("Ymd");
    $encabezado = $encabezado . str_repeat(" ", 133);
    $nombre = "transferencias-" .$fecha->getTimestamp(). ".txt";
    $gestor = fopen($nombre, "w");
    fwrite($gestor, $encabezado);
    fwrite($gestor, "\r\n");
    for($fila=2; $fila < $maxFila+1; $fila++){
        try{
            $cbu = preg_replace("/[^0-9]/", "", $hoja->getCellByColumnAndRow(1, $fila)->getFormattedValue());
            
            $importe = $hoja->getCellByColumnAndRow(2, $fila)->getValue();
            
            if(is_float($importe)){
                $importe = number_format($importe, 2);    
            }
            else{
                throw new Exception("ERROR IMPORTE");    
            }
            
            $importeTransformado = str_replace(",","",$importe);      
            $cuenta = new cuentaTransferencia($cbu, $importeTransformado);
            array_push($arrayCuentas, $cuenta);
            fwrite($gestor, $cuenta->generarLineaTransferenciaProveedores());
            fwrite($gestor, "\r\n");

        }
        catch(Exception $e){
            $linea = array($cbu,$importe, $fila, $e->getMessage());
            array_push($arrayErrores, $linea);
        }


    }
    fclose($gestor);
    $retorno['cuentas'] = $arrayCuentas;
    $retorno['errores'] = $arrayErrores;
    $retorno['link']= $nombre;
    $retorno['coma']= $coma;
    return $response->withJson($retorno);    
});
$app->get('/v2/generarTransferenciasProveedores', function (Request $request, Response $response, array $args){
    $arrayCuentas = array();
    $arrayErrores = array();
    $nombreArchivo = $_FILES['archivo']['tmp_name'];
    $extension = pathinfo($_FILES['archivo']['name'], PATHINFO_EXTENSION);
    $extension  =strtolower($extension);
    switch ($extension) {
        case 'xlsx':
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            break;
        case 'xls':
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
            break;
        case 'ods':
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Ods();
            break;
        default:
            return $response->withJson('Formato de archivo no soportado', 400);
            break;
    }
    $archivo = $reader->load($nombreArchivo);
    $hoja = $archivo->getActiveSheet();
    $maxFila = $hoja->getHighestRow();
    $fecha = new DateTime();
    $cbuPropio = $_POST['cbuPropio'];
    $consolidado = $_POST['consolidado'];
    $encabezado = "*U*". $cbuPropio."D".date('Ymd').$consolidado."OBSER".str_repeat(" ", 64 - strlen("SOBSER")) .date("Ymd");
    $encabezado = $encabezado . str_repeat(" ", 133);
    $nombre = "transferencias-".$fecha->getTimestamp() . ".txt";
    $gestor = fopen($nombre, "w");
    fwrite($gestor, $encabezado);
    fwrite($gestor, "\r\n");
    for($fila=2; $fila < $maxFila+1; $fila++){
        try{
            $cbu = preg_replace("/[^0-9]/", "", $hoja->getCellByColumnAndRow(1, $fila)->getFormattedValue());
            
            $importe = $hoja->getCellByColumnAndRow(2, $fila)->getValue();
            
            if(is_float($importe)){
                $importe = number_format($importe, 2);    
            }
            else{
                throw new Exception("ERROR IMPORTE");    
            }
            
            $importeTransformado = str_replace(",","",$importe);      
            $cuenta = new cuentaTransferencia($cbu, $importeTransformado);
            array_push($arrayCuentas, $cuenta);
            fwrite($gestor, $cuenta->generarLineaTransferenciaProveedores());
            fwrite($gestor, "\r\n");

        }
        catch(Exception $e){
            $linea = array($cbu,$importe, $fila, $e->getMessage());
            array_push($arrayErrores, $linea);
        }


    }
    fclose($gestor);
    $retorno['cuentas'] = $arrayCuentas;
    $retorno['errores'] = $arrayErrores;
    $retorno['link']= $nombre;
    $retorno['coma']= $coma;
    return $response->withJson($retorno);    
    
});

$app->get('/descargar', function (Request $request, Response $response, array $args){
    $archivo = $_GET['archivo'];
    $nuevaRespuesta = $response->withHeader('Content-Description', 'File Transfer')
    ->withHeader('Content-Type', 'application/octet-stream')
    ->withHeader('Content-Disposition', 'attachment;filename="'.basename($archivo).'"')
    ->withHeader('Expires', '0')
    ->withHeader('Cache-Control', 'must-revalidate')
    ->withHeader('Pragma', 'public')
    ->withHeader('Content-Length', filesize($archivo));
    readfile($archivo);
    return $nuevaRespuesta;
});


$app->run();

