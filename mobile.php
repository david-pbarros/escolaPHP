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
					$response = $response .$key .' : "' .urlencode($val) .'", ';
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
			$results = queryListResult('SELECT id, senha, nome, bloqueado FROM usuario WHERE dataexclusao IS NULL)', null);
			
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
			$results = queryListResult('SELECT id, data, fonte, numero, sala, status, a.nome AS ajudante, es.nome AS estudante, e.nrestudo FROM designacao d '
										.'INNER JOIN estudante es ON es.id = d.estudante_id '
										.'LEFT JOIN ajudante a ON a.id = d.ajudante_id '
										.'LEFT JOIN designacao_estudo e ON e.designacao_id = d.id '
										.'WHERE dataexclusao IS NULL AND data > :data', array("data"=>getFormatedDateTime($params['data_ultima'])));
			
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
						} else if (($key == 'ajudante' || $key == 'estudante') && ($val == '' || $val == NULL || $val == 'null')) {
							$val = '""';
                                                        
						} else if ($key == 'fonte') {
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
	
	
?>