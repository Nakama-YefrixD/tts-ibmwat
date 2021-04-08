#!/usr/bin/php
<?php
    require_once ('phpagi.php');
    include('httpful.phar');
    set_time_limit(120);

    // URL DE WEB SERVICE
    $urlWebService = "http://190.13.140.188/ws_coopelan/ingresa_lectura.php";

    // AUDIOS .
    $rutaPrincipalAudios = "/var/lib/asterisk/sound/audios/";

    $ErrorLectura        = $rutaPrincipalAudios."ErrorLectura.mp3";
    $LecturaIngresada    = $rutaPrincipalAudios."LecturaIngresada.mp3";
    $Lecturamedidor      = $rutaPrincipalAudios."Lecturamedidor.mp3";
    $NumeroClientemp3_xc = $rutaPrincipalAudios."NumeroClientemp3_xc.wav";
    $NumeroMALingresado  = $rutaPrincipalAudios."NumeroMALingresado.mp3";

    // VARIABLES
    $numeroIntentos = 3;
    $contador = 0;
    $repetir  = true;

    $agi = new AGI();
    $agi->answer();

    while($repetir == true){

        if($contador == $numeroIntentos){
            $repetir = false;
        }else{
            $contador = $contador + 1;
        }

        $numeroCliente = $agi->get_data($NumeroClientemp3_xc, 20000, 7);

        if(isset($numeroCliente['data']) && $numeroCliente['data'] === 'timeout'){
            $agi->verbose("No marco nada, o ingreso mas de 7 digitos");
            $agi->stream_file($NumeroMALingresado);
        }else{
            $agi->verbose("número del cliente: ".$numeroCliente['result']);
            $codigo = consultarWs($agi, $numeroCliente['result'], $urlWebService);

            $agi->verbose("Codigo del resultado de la web service");
            $agi->verbose($codigo);        

            if($codigo == 0){

                $lecturaCliente = $agi->get_data($Lecturamedidor, 20000, 5);

                if(isset($numeroCliente['data']) && $numeroCliente['data'] === 'timeout'){
                    $agi->verbose("No marco nada en lectura, o ingreso mas de 5 digitos");
                    $agi->stream_file($ErrorLectura);
                    $agi->hangup();
                }else{
                    $agi->stream_file($LecturaIngresada);
                    $agi->hangup();
                }


            }else{

                $agi->verbose("El número del cliente es incorrecto cod: ".$codigo);
                $agi->stream_file($NumeroMALingresado);

            }
        }
    }

    function consultarWs($agi, $cliente, $url){

        $agi->verbose("Consultando web service");
        $codigo = 0;

        try{

            $data =  array('servicio' => "$cliente");
                
            $dataJson = json_encode($data);
                        
            $result = \Httpful\Request::post($url)
                            ->sendsJson()
                            ->body($dataJson)
                            ->send();
        
            
            $agi->verbose("Mostrando el resultado de la webservice");
            $retorno = json_decode($result->body);
            $agi->verbose($result->body);
            $codigo = $result->body->cod;

        }catch(Exception $e){
            $agi->verbose("Lo sentimos, ocurrio un error al momento de consultar la web service: ");
            $agi->verbose($e);
        }

        return $codigo;

    }
    
?>