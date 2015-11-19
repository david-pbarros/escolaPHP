<?php header ('Content-type: text/html; charset=UTF-8'); 
	require_once 'DAO.php';
	require_once 'security.php';

	function mobile_estudo_list($params) {
		iniciaTransacaoMobile($params['hash']);
                
        try {
            $results = queryListResult('SELECT nrestudo, descricao FROM estudo WHERE dataexclusao IS NULL', null);
			
			$response = '{"response" : "OK", "itens" : [';
			
			foreach($results as $result) {
				$response = $response .'{';

				foreach($result as $key=>$val) {
					if (!is_numeric ( $key )) {			
						$response = $response .$key .' : "' .urlencode($val) .'", ';
					}
				}
				
				$response = rtrim($response, ", ") .'},';
			}
			
			echo rtrim($response, ",") .']}';
			
		} catch(Exception $e) {
			echo '{"response" : "ERRO", "codigo" : "' .$e->getCode() .'", "mensagem" : "' .$e->getMessage() .'", "arquivo:" : "' .$e->getFile() .'", "linha:" : "' .$e->getLine() .'"}';
		}
	}
	
	function usuario($params) {
		iniciaTransacaoMobile($params['hash']);
		
		$nome = "";
		
		try {
			$results = queryListResult('SELECT id, senha, nome, bloqueado FROM usuario', null);
			
			$response = '{"response" : "OK", "itens" : [';
			
			foreach($results as $result) {
				$response = $response .'{';
				foreach($result as $key=>$val) {
					if (!is_numeric ( $key )) {
						if ($key == 'senha') {
							$val = '"' .$val .'"';
							
						} else if ($key == 'nome' ) {
							$val = urlencode($val);
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
	
	function mobile_designacao_list($params) {
		iniciaTransacaoMobile($params['hash']);
		
				
		try {
			$results = queryListResult('SELECT d.id, data, fonte, numero, sala, status, d.dtultimaatualiza, d.tempo, a.nome AS ajudante, es.nome AS estudante, e.estudo_id AS nrestudo FROM designacao d '
										.'INNER JOIN estudante es ON es.id = d.estudante_id '
										.'LEFT JOIN ajudante a ON a.id = d.ajudante_id '
										.'LEFT JOIN designacao_estudo e ON e.designacao_id = d.id ' 
										.'WHERE d.dataexclusao IS NULL  AND data >= :data', array("data"=>getFormatedDate($params['data_ultima'])));
			
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
						} else if ($key == 'dtultimaatualiza') {
							if ($val != NULL) {
								$val = strtotime($val);
								$val   = '"' .date('d/m/Y H:i:s',$val) .'"';
							
							} else {
								$val = '""';
							}
						} else {
								if ($val == '' || $val == NULL || $val == 'null') {
										$val = '""';
								
								} else {
										$val = '"' .urlencode($val) .'"';
								}
						}
						
						$response = $response .'"' .$key .'" : ' .$val .', ';
					}
				}
				
				$response = rtrim($response, ", ") .'},';
			}
			
			echo rtrim($response, ",") .']}';
			
		} catch(Exception $e) {
			echo '{"response" : "ERRO", "mensagem" :' .$e->getMessage() .'}';
		}
	}
	
	function atualiza_designacao($params) {
		iniciaTransacaoMobile($params['hash']);
		
		$queryParam = array("id"=>$params['id'], "dtultimaatualiza"=>date('Y-m-d H:i:s'), "status"=>$params['status'], "tempo"=>$params['tempo']);
		
		try {
			createQuery('UPDATE designacao SET  dtultimaatualiza = :dtultimaatualiza, status = :status, tempo = :tempo WHERE id = :id', $queryParam);
			
			echo '{"response":"OK"}';
			
		} catch(Exception $e) {
			echo '{"response" : "ERRO", "mensagem" :' .$e->getMessage() .'}';
		}
	}
?>