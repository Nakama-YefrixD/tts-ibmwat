#!/usr/bin/php
<?php

require_once ('phpagi.php');
$agi = new AGI();


function consultaIbm($qr){
        $apiKey = 'R_3F1fdc_XkJ9WX3VZ6S8y-L_q_wN_QOz1BGmGTJdVaH';
        $apiUrl = 'https://api.us-south.text-to-speech.watson.cloud.ibm.com/instances/7574d201-24f6-40ce-89c3-f4c816ba7edd/v1/synthesize?accept=audio%2Fmulaw%3Brate%3D8000&text='.$qr.'&voice=es-ES_EnriqueV3Voice';
    
        $curl = curl_init();
        curl_setopt ( $curl, CURLOPT_URL, $apiUrl);
        curl_setopt ( $curl, CURLOPT_HTTPGET, 1 );
        curl_setopt( $curl, CURLOPT_USERPWD, 'apikey'.':'.$apiKey);
        curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1);
        $result =  curl_exec( $curl);

        return $result;
}

// LLamada a la api de consultas
$wDir="/var/lib/asterisk/sounds/en/gravaciones/citas/tts/";
if (isset($argv[1])){
    $head = array();
    $head[] = 'Content-type: application/json';
    //$head[] = 'Authorization: ApiUser Aitue-web1q2w3e4r.,';
    $username = 'ApiUser';
    $password = 'Aitue-web1q2w3e4r.,';
    $query = $argv[1];
    $urlConsulta = 'https://projects.sofgem.cl/consulta/atencion/?rut='.$query;
    
    $ch = curl_init();
    curl_setopt ( $ch , CURLOPT_URL, $urlConsulta);
    curl_setopt ( $ch , CURLOPT_HTTPGET, 1 );
    curl_setopt( $ch , CURLOPT_USERPWD, $username . ":" . $password);
    curl_setopt ( $ch , CURLOPT_HTTPHEADER, $head);
    curl_setopt( $ch , CURLOPT_RETURNTRANSFER, 1);
    $data = curl_exec($ch );
    curl_close($ch );
    $newData = json_decode($data, true);
    
    if( $newData['estado'] == true){
        $fecha = $newData['datos']['fecha'];
        $hora = $newData['datos']['Hora'];
        $medico = $newData['datos']['medico'];
        $especialidad = $newData['datos']['especialidad'];
        $paciente = $newData['datos']['paciente'];
        $correo_paciente = $newData['datos']['correo_paciente'];
        $mesage = 'medico, '.$medico. ', especialidad, '.$especialidad.', paciente, '.$paciente.', correo del paciente, '.$correo_paciente;
        $encodeMesage = urlencode($mesage);
        
        $result = consultaIbm($encodeMesage);
        echo("listo");
        $wr = fopen($wDir.'audio.ulaw',"w");
        fwrite($wr, $result);
        play_date($fecha,$hora,$agi);
        $agi->answer();
        $agi->stream_file($wDir."audio");
    }else{
        echo $newData['mensaje'];
        $message = $newData['mensaje'];
        $encodeMesage = urlencode($message);
        $result = consultaIbm($encodeMesage);
        $wr = fopen($wDir.'audio.ulaw',"w");
        fwrite($wr, $result);
        $agi->answer();
        $agi->stream_file($wDir."audio");

    }
}else{
    echo "Ingrese rut";
    $message = "Ingrese rut";
    $encodeMesage = urlencode($message);
    $result = consultaIbm($encodeMesage);
    $wr = fopen($wDir.'audio.ulaw',"w");
    fwrite($wr, $result);
    $agi->answer();
    $agi->stream_file($wDir."audio");
}


function play_date($fechaP,$horaP,$agi){
    $tFecha = explode(" ", $fechaP);
    $tHora = explode(" ", $horaP);

    $td = explode("/", $tFecha[0]);
    $dia = intval("${td[0]}");
    $mes = intval("${td[1]}") - 1;

    $th = explode(":", $tHora[0]);
    $hora = $th[0] ? intval("${th[0]}") : 0;
    $minuto = $th[1] ? intval("${th[1]}") : 0;

    $agi->answer();
    PlayTimePart($agi,$dia);
    $agi->stream_file("digits/pt-de");
    $agi->stream_file("digits/mon-".$mes);
    $agi->stream_file("digits/pt-as");
    PlayTimePart($agi,$hora);
    $agi->stream_file("letters/e");
    PlayTimePart($agi,$minuto);
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


?>