<?php header ('Content-type: text/html; charset=UTF-8'); 
	require_once 'DAO.php';
	require_once 'security.php';
	
	function obtemIdSemana($params) {
		return $result = querySingleResult('SELECT id FROM semanadesignacao WHERE data = :data', array("data"=>getFormatedDate($params['data'])));
	}
	
	function semana_insert($params) {
		iniciaTransacao($params['hash']);
		
		try {
			$result = obtemIdSemana($params);
			
			if(!empty($result)) {
				echo '{"response" : "existente", "id_online" : ' .$result->id .'}';
			
			} else {
                                $sequence = obtemSequence('semanadesignacao');
                                
				$queryParam = array("id"=>$sequence, "dtultimaatualiza"=>date('Y-m-d H:i:s'), "assebleia"=>$params['assebleia'], "data"=>getFormatedDate($params['data']),
										"recapitulacao"=>$params['recapitulacao'], "semreuniao"=>$params['semReuniao'], "visita"=>$params['visita'], "mes_id"=>$params['mes']);
				
				createQuery('INSERT INTO semanadesignacao(id, assebleia, data, dtultimaatualiza, recapitulacao, semreuniao, visita, mes_id) VALUES (:id, :assebleia, :data, :dtultimaatualiza, :recapitulacao, :semreuniao, :visita, :mes_id)', $queryParam);
				
				echo '{"response" : "novo", "id_online" : ' .$sequence .'}';
			}
		} catch(Exception $e) {
			echo '{"response" : "ERRO", "codigo" : "' .$e->getCode() .'", "mensagem" : "' .$e->getMessage() .'", "arquivo:" : "' .$e->getFile() .'", "linha:" : "' .$e->getLine() .'"}';
		}
	}
	
	function semana_update($params) {
		iniciaTransacao($params['hash']);
		
		$queryParam = array("id"=>$params['id'], "dtultimaatualiza"=>date('Y-m-d H:i:s'), "assebleia"=>$params['assebleia'], "recapitulacao"=>$params['recapitulacao'],
								"semreuniao"=>$params['semReuniao'], "visita"=>$params['visita']);
		
		try {
			createQuery('UPDATE semanadesignacao SET assebleia = :assebleia, dtultimaatualiza = :dtultimaatualiza, recapitulacao = :recapitulacao, semreuniao = :semreuniao, visita = :visita WHERE id = :id', $queryParam);
			echo '{"response":"OK"}';
			
		} catch(Exception $e) {
			echo '{"response" : "ERRO", "codigo" : "' .$e->getCode() .'", "mensagem" : "' .$e->getMessage() .'", "arquivo:" : "' .$e->getFile() .'", "linha:" : "' .$e->getLine() .'"}';
		}
	}
	
	function semana_list($params) {
		iniciaTransacao($params['hash']);
		
		try {
			$results = queryListResult('SELECT id, assebleia, data, recapitulacao, semreuniao, visita, mes_id FROM semanadesignacao WHERE dtultimaatualiza > :data', array("data"=>getFormatedDate($params['data_ultima'])));
			
			$response = '{"response" : "OK", "itens" : [';
			
			foreach($results as $result) {
				$response = $response .'{';
				foreach($result as $key=>$val) {
					if (!is_numeric ( $key )) {
						if ($key == 'data') {
							if ($val != NULL) {
								$val = strtotime($val );
								$val   = '"' .date('d/m/Y',$val) .'"';
							
							} else {
								$val = '""';
							}
						}
						
						$response = $response .'"' .$key .'" : ' .$val .', ';
					}
				}
				
				$response = rtrim($response, ", ") .'},';
			}
			
			echo rtrim($response, ",") .']}';
			
		} catch(Exception $e) {
			echo '{"response" : "ERRO", "codigo" : "' .$e->getCode() .'", "mensagem" : "' .$e->getMessage() .'", "arquivo:" : "' .$e->getFile() .'", "linha:" : "' .$e->getLine() .'"}';
		}
	}
?>