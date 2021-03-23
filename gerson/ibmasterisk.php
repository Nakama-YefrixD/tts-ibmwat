#!/usr/bin/php
<?php
	// External libraries
	require_once ('phpagi.php');
	//include('httpful.phar');
	set_time_limit(120);
	

	// LLamada a la api de consultas
	$direccionAudiosTts="/var/lib/asterisk/sounds/en/gravaciones/citas/tts/";

	// $audioSiete         = "/var/lib/asterisk/sounds/en/gravaciones/audios/Audio7rutconhoramp3_xc";
	// $audioSieteDosUno   = "/var/lib/asterisk/sounds/en/gravaciones/audios/Audio721mp3_xc";
	// $audioSieteUno      = "/var/lib/asterisk/sounds/en/gravaciones/audios/Audio71conDRmp3_xc";
	// $audioTres          = "/var/lib/asterisk/sounds/en/gravaciones/audios/Audio3num1mp3_xc";
	// $audioSieteTres     = "/var/lib/asterisk/sounds/en/gravaciones/audios/Audio73esperaenlineamp3_xc";
	// $audioSeis          = "/var/lib/asterisk/sounds/en/gravaciones/audios/Audio6rutsinhoramp3_xc";
	// $audioSieteDos      = "/var/lib/asterisk/sounds/en/gravaciones/audios/Audio72confirmacionreservamp3_xc";

	$audioSiete         = "/opt/xcontact/audios/Bienvenida/Audio7rutconhoramp3_xc";
	$audioSieteDosUno   = "/opt/xcontact/audios/Bienvenida/Audio721mp3_xc";
	$audioSieteUno      = "/opt/xcontact/audios/Bienvenida/Audio71conDRmp3_xc";
	$audioTres          = "/opt/xcontact/audios/Bienvenida/Audio3num1mp3_xc";
	$audioSieteTres     = "/opt/xcontact/audios/Bienvenida/Audio73esperaenlineamp3_xc";
	$audioSeis          = "/opt/xcontact/audios/Bienvenida/Audio6rutsinhoramp3_xc";
	$audioSieteDos      = "/opt/xcontact/audios/Bienvenida/Audio72confirmacionreservamp3_xc";

	// Recepci�n de RUT del cliente
	$agi 			= new AGI();
	$cpf 			= $agi->get_data("/opt/xcontact/audios/Bienvenida/Audio4num2mp3_xc",20000,9);
	// $cpf 			= $agi->get_data("/var/lib/asterisk/sounds/en/gravaciones/audios/Audio7rutconhoramp3_xc",20000,9);
	
	if(isset($cpf['data']) && $cpf['data'] === 'timeout')
	{	
		$agi->verbose("Fim da URA interativa, ERRO: Usuario demorou para informar o CPF ou n�o informou os 11 digitos");
		$agi->stream_file("/var/gravacoes/Xcontact/ura_reset_senha/DesculpePrecisaInformarCPFmp3_xc");
		$agi->hangup();
		exit();
	}
	else
	{
		$agi->verbose("Buscando CPF: ".$cpf['result']);
		$rutCliente		= $cpf['result'];
		$query		= $rutCliente;
		
        $head = array();
        $head[] = 'Content-type: application/json';
        $username = 'ApiUser';
        $password = 'Aitue-web1q2w3e4r.,';
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
            

            $meses = array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");

            $diaActualizacion = date("j", strtotime($fecha));
            $mesActualizacion = $meses[date('n', strtotime($fecha))-1];
            $anioActualizacion = date("Y", strtotime($fecha));
            $horaActualizacion = date("H", strtotime($hora));
            $minutoActualizacion = date("i", strtotime($hora));

            $frase = 'El '.$diaActualizacion.' de '.$mesActualizacion.', a las '.$horaActualizacion.' con '.$minutoActualizacion.' minutos, ';

    
            $encodeMesage = urlencode($frase." con el especialista, ".$mesage);


            $result = consultaIbm($encodeMesage);
            $wr = fopen($direccionAudiosTts.'audio.ulaw',"w");
            fwrite($wr, $result);


            $agi->stream_file($audioSiete);
           
            $agi->answer();

            $agi->stream_file($direccionAudiosTts."audio");
			$agi->stream_file($audioSieteDos);
			$agi->stream_file($audioSieteTres);
        }else{
			$agi->stream_file($audioSeis);
    
        }
	}
	

    function consultaIbm($qr){
        // $apiKey = 'R_3F1fdc_XkJ9WX3VZ6S8y-L_q_wN_QOz1BGmGTJdVaH';
        $apiKey = 'oVKfEQBup_mpTfeJjqg6__EKSQLp_YsJpmILWYdugx_y';
        // $apiUrl = 'https://api.us-south.text-to-speech.watson.cloud.ibm.com/instances/7574d201-24f6-40ce-89c3-f4c816ba7edd/v1/synthesize?accept=audio%2Fmulaw%3Brate%3D8000&text='.$qr.'&voice=es-ES_EnriqueV3Voice';
        // $apiUrl = 'https://api.us-east.text-to-speech.watson.cloud.ibm.com/instances/490bb45d-0965-466b-88b8-da1195ccba89/v1/synthesize?accept=audio%2Fmulaw%3Brate%3D8000&text='.$qr.'&voice=es-ES_EnriqueV3Voice';
        $apiUrl = 'https://api.us-east.text-to-speech.watson.cloud.ibm.com/instances/490bb45d-0965-466b-88b8-da1195ccba89/v1/synthesize?accept=audio%2Fmulaw%3Brate%3D8000&text='.$qr.'&voice=es-LA_SofiaVoice';

        $curl = curl_init();
        curl_setopt ( $curl, CURLOPT_URL, $apiUrl);
        curl_setopt ( $curl, CURLOPT_HTTPGET, 1 );
        curl_setopt( $curl, CURLOPT_USERPWD, 'apikey'.':'.$apiKey);
        curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1);
        $result =  curl_exec( $curl);
        // echo "ibm";
        // echo $result;
        return $result;
    }
?>
