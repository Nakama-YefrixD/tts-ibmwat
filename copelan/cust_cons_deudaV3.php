#!/usr/bin/php
<?php
require_once ('phpagi.php');
include('httpful.phar');
set_time_limit(120);

$agi = new AGI();
$contador = 1;

// $identCliente = $agi->get_variable("V_IDENTIFICADOR")['data'];
$identCliente = "5040906";
$agi->verbose("Identificacor del cliente: " . $identCliente);

//#################### Fun��es ##########################################
function PlayData($agi,$tempo){
        //$tempo = $argv[1] ? $argv[1] : date("d/m H:i");
        // Formato: 15/3 15:31
            $agi->verbose("Entrei na funcao Playdata");
        $t = explode(" ", $tempo);

        $td = explode("/", $t[0]);
        $dia = intval("${td[0]}");
        $mes = intval("${td[1]}") - 1;

        // Trata tempo
    //	$th = explode(":", $t[1]);
    //	$hora = intval("${th[0]}");
    //	$minuto = intval("${th[1]}");

        PlayTimePart($agi,$dia);
        $agi->stream_file("digits/de");
        $agi->stream_file("digits/mon-".$mes);
    //	$agi->stream_file("digits/pt-as");
    //	PlayTimePart($agi,$hora);
    //	$agi->stream_file("letters/e");
    //	PlayTimePart($agi,$minuto);
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

    $agi->verbose("Entrando na funcao consulta");

    try{

            $uri = "http://20.25.1.82/ws_coopelan/consulta_deuda.php";
                    
            $data =  array('servicio' => "$identCliente");
            
            $data_json = json_encode($data);

            $agi->verbose("data enviado");
            $agi->verbose(json_decode($data_json));

            $result = \Httpful\Request::post($uri)
                            ->sendsJson()
                            ->body($data_json)
                            ->send();
        
            
            $agi->verbose("Mostrando o JSON");
            //$agi->verbose($result);
            $retorno = json_decode($result->body);
            
            //$agi->verbose($retorno);    
            $agi->verbose($result->body);
        
            $obj = new stdClass;
            $obj->cod = $result->body->cod;
            $obj->audio = $result->body->audio;
            $obj->audio2 = $result->body->audio2;
            $obj->ult_fecha_emi = $retorno->ult_fecha_emi;
            $obj->ult_fecha_ven = $retorno->ult_fecha_ven;
            $obj->ult_fecha_corte = $retorno->ult_fecha_corte;
            $obj->monto_pagar = $retorno->monto_pagar;
            $obj->deuda_vencida = $retorno->deuda_vencida;
            $obj->deuda_por_vencer = $retorno->deuda_por_vencer;
            $obj->mes_prox_corte = $retorno->mes_prox_corte;
            $obj->cantidad_doctos = $retorno->cantidad_doctos;  
            
            return $obj;
    

    }catch(Exception $e){

            $agi->verbose("Oocorreu um erro");

    }
}

function AvaliaFluxo($agi,$identCliente,$contador){

    $ret = Consulta($agi,$identCliente,$contador);
    
    //    $agi->set_variable("Cod",$ret->dst_cod); 
    //    $agi->set_variable("Audio",$ret->dst_audio); 
    //    $agi->set_variable("Numero",$ret->dst_numero_cliente);
    //    $agi->set_variable("Nombre",$ret->dst_nombre);
    //    $agi->set_variable("Direccion",$ret->dst_direccion);
    //   $agi->set_variable("Observacion",$ret->dst_obs);

     

    
        $agi->verbose("Cod para if= ",$ret->cod);
        
        $agi->verbose("$ret"); //2
    
        If ($ret->cod == '2'){
            $agi->verbose("Cod 2 dentro do IF");
            $agi->stream_file('/gravacoes/Xcontact/audios/Integracion/AUDIO4mp3_xc');
            $agi->stream_file('/gravacoes/Xcontact/audios/Integracion/AUDIO17mp3_xc');
            $agi->exec('Hangup');
            exit();
            
        }elseif($ret->cod == '3'){
            $agi->verbose("Cod 3 dentro do IF");
            $agi->stream_file('/gravacoes/Xcontact/audios/Integracion/AUDIO55mp3_xc');
            $agi->stream_file('/gravacoes/Xcontact/audios/Integracion/AUDIO20mp3_xc');
            $agi->exec('SayNumber',$ret->cantidad_doctos);
            $agi->stream_file('/gravacoes/Xcontact/audios/Integracion/AUDIO21mp3_xc');
            $agi->stream_file('/gravacoes/Xcontact/audios/Integracion/AUDIO23mp3_xc');
            $agi->exec('SayNumber',$ret->monto_pagar);
            $agi->stream_file('/gravacoes/Xcontact/audios/Integracion/AUDIO9mp3_xc');
            $agi->stream_file('/gravacoes/Xcontact/audios/Integracion/AUDIO26mp3_xc');
    //        $agi->verbose("Mes do corte: ");
    //        $agi->verbose($ret->mes_prox_corte);
    //        $agi->stream_file("digits/".$ret->mes_prox_corte);
    //        $agi->stream_file('/gravacoes/Xcontact/audios/Integracion/AUDIO17mp3_xc');
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
    //        $agi->stream_file("digits/".$ret->mes_prox_corte);
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


$agi->verbose("Inicio Chamando a funcao de avalair fluxo.");
AvaliaFluxo($agi,$identCliente,$contador);
exit();

?>