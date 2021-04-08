#!/usr/bin/php
<?php
    require_once ('phpagi.php');
    include('httpful.phar');
    set_time_limit(120);

    $agi = new AGI();
    $contador = 1;

    $identCliente = $agi->get_variable("V_IDENTIFICADOR")['data'];
    $agi->verbose("Cliente enviado: " . $identCliente);

    function PlayData($agi,$tempo){
        $agi->verbose("Entrei na funcao Playdata");
        $t = explode(" ", $tempo);

        $td = explode("/", $t[0]);
        $dia = intval("${td[0]}");
        $mes = intval("${td[1]}") - 1;
        PlayTimePart($agi,$dia);
        $agi->stream_file("digits/de");
        $agi->stream_file("digits/mon-".$mes);
    }

    function PlayTimePart($agi,$t1){
        if($t1 > 20){
            if(substr($t1, -1) == 0){
                $agi->stream_file("digits/".$t1);
            }
            else {
                $agi->stream_file("digits/".substr($t1,0,1)."0");
                $agi->stream_file("letters/e");
                $agi->stream_file("digits/".substr($t1, -1));
            }
        }
        else {
            $agi->stream_file("digits/".$t1);
        }
    }

    function Consulta($agi,$identCliente,$contador){

        $agi->verbose("Consultando a la web service deuda");

        try{

                $uri = "http://20.25.1.82/ws_coopelan/consulta_deuda.php";
                        
                $data =  array('servicio' => "$identCliente");
                
                $data_json = json_encode($data);
                            
                $result = \Httpful\Request::post($uri)
                                ->sendsJson()
                                ->body($data_json)
                                ->send();
            
                
                $agi->verbose("Mostrando el resultado de la webservice");
                //$agi->verbose($result);
                $retorno = json_decode($result->body);
                
                //$agi->verbose($retorno);    
                $agi->verbose($result->body);
            
                $obj = new stdClass;
                $obj->cod = $result->body->cod;
                $obj->audio = $result->body->audio;
                $obj->audio2 = $result->body->audio2;
                $obj->ult_fecha_emi = $result->body->ult_fecha_emi;
                $obj->ult_fecha_ven = $result->body->ult_fecha_ven;
                $obj->ult_fecha_corte = $result->body->ult_fecha_corte;
                $obj->monto_pagar = $result->body->monto_pagar;
                $obj->deuda_vencida = $result->body->deuda_vencida;
                $obj->deuda_por_vencer = $result->body->deuda_por_vencer;
                $obj->mes_prox_corte = $result->body->mes_prox_corte;
                $obj->cantidad_doctos = $result->body->cantidad_doctos;   
                
                return $obj;
        

        }catch(Exception $e){

                $agi->verbose("Lo sentimos, ocurrio un error al momento de consultar la web service de deuda");

        }
    }

    function funFlujoAudios($agi,$identCliente,$contador){

        $ret = Consulta($agi,$identCliente,$contador);

        $agi->verbose("Codigo de la web service = ",$ret->cod);
        
        // $agi->verbose("$ret"); //2
    
        If ($ret->cod == '2'){
            $agi->verbose("Cod 2 del if ejecuentando: AUDIO4mp3_xc y AUDIO17mp3_xc");
            $agi->stream_file('/gravacoes/Xcontact/audios/Integracion/AUDIO4mp3_xc');
            $agi->stream_file('/gravacoes/Xcontact/audios/Integracion/AUDIO17mp3_xc');
            $agi->exec('Hangup');
            exit();
            
        }elseif($ret->cod == '3'){
            $agi->verbose("Cod 3 del if ejecuentando");
            $agi->stream_file('/gravacoes/Xcontact/audios/Integracion/AUDIO55mp3_xc');
            $agi->stream_file('/gravacoes/Xcontact/audios/Integracion/AUDIO20mp3_xc');
            $agi->exec('SayNumber',$ret->cantidad_doctos);
            $agi->stream_file('/gravacoes/Xcontact/audios/Integracion/AUDIO21mp3_xc');
            $agi->stream_file('/gravacoes/Xcontact/audios/Integracion/AUDIO23mp3_xc');
            $agi->exec('SayNumber',$ret->monto_pagar);
            $agi->stream_file('/gravacoes/Xcontact/audios/Integracion/AUDIO9mp3_xc');
            $agi->stream_file('/gravacoes/Xcontact/audios/Integracion/AUDIO26mp3_xc');
            $agi->exec('Hangup');
            
        }elseif($ret->cod == '4'){   
            $agi->verbose("Cod 4 dentro do IF");
            $agi->stream_file('/gravacoes/Xcontact/audios/Integracion/AUDIO20mp3_xc');
            $agi->exec('SayNumber',$ret->cantidad_doctos);
            $agi->stream_file('/gravacoes/Xcontact/audios/Integracion/AUDIO21mp3_xc');
            $agi->stream_file('/gravacoes/Xcontact/audios/Integracion/AUDIO23mp3_xc');
            $agi->exec('SayNumber',$ret->monto_pagar);
            $agi->stream_file('/gravacoes/Xcontact/audios/Integracion/AUDIO9mp3_xc');
            $agi->stream_file('/gravacoes/Xcontact/audios/Integracion/AUDIO16mp3_xc');
            $agi->verbose("Mes do corte: ");
            $agi->verbose($ret->mes_prox_corte);
            $tempo = $ret->mes_prox_corte;
            $agi->verbose($tempo);
            PlayData($agi,$tempo);
            $agi->stream_file('/gravacoes/Xcontact/audios/Integracion/AUDIO17mp3_xc');

        }else{
            $agi->verbose("Retorno invalido = {$ret->cod}");
            exit();
        }
        exit();
    }


    $agi->verbose("Iniciamos flujo de secuencia de audios");
    funFlujoAudios($agi,$identCliente,$contador);
    exit();

?>