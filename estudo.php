<?php header ('Content-type: text/html; charset=UTF-8'); 
	require_once 'DAO.php';
	require_once 'security.php';
	
	function existeEstudo($params) {
		return $result = querySingleResult('SELECT 1 FROM estudo WHERE dataexclusao IS NULL AND nrestudo = :nrestudo', array("nrestudo"=>$params['numero']));	
	}
	
	function estudo_insert($params) {
		iniciaTransacao($params['hash']);
		
		try {
			$result = existeEstudo($params);
			
			$queryParam = array("nrestudo"=>$params['numero'], "dtultimaatualiza"=>date('Y-m-d H:i:s'), "descricao"=>urldecode($params['descricao']), "leitura"=>$params['leitura'], 
									"demonstracao"=>$params['demonstracao'], "discurso"=>$params['discurso']);
					
			if(empty($result)) {
				createQuery('INSERT INTO estudo(nrestudo, demonstracao, descricao, discurso, dtultimaatualiza, leitura) VALUES (:nrestudo, :demonstracao, :descricao, :discurso, :dtultimaatualiza, :leitura)', $queryParam);
				echo '{"response" : "OK"}';
                                
			} else {
				createQuery('UPDATE estudo SET descricao = :descricao, leitura = :leitura, demonstracao = :demonstracao, discurso = :discurso, dtultimaatualiza = :dtultimaatualiza WHERE nrestudo = :nrestudo', $queryParam);
                                echo '{"response" : "existente"}';
            }
			
		} catch(Exception $e) {
			echo '{"response" : "ERRO", "codigo" : "' .$e->getCode() .'", "mensagem" : "' .$e->getMessage() .'", "arquivo:" : "' .$e->getFile() .'", "linha:" : "' .$e->getLine() .'"}';
		}
	}
	
	function estudo_list($params) {
        	iniciaTransacao($params['hash']);
                
                try {
                        $results = queryListResult('SELECT nrestudo, demonstracao, descricao, discurso, leitura FROM estudo WHERE dataexclusao IS NULL AND dtultimaatualiza > :data', array("data"=>getFormatedDateTime($params['data_ultima'])));
			
			$response = '{"response" : "OK", "itens" : [';
			
			foreach($results as $result) {
				$response = $response .'{';

				foreach($result as $key=>$val) {
					if (!is_numeric ( $key )) {
						$response = $response .$key .' : "' .str_replace(" ","%20",$val) .'", ';
					
					} else if ($key == 'descricao' ) {
						$response = $response .$key .' : ' .urlencode($val) .', ';
					}
				}
				
				$response = rtrim($response, ", ") .'},';
			}
			
			echo rtrim($response, ",") .']}';
			
		} catch(Exception $e) {
			echo '{"response" : "ERRO", "codigo" : "' .$e->getCode() .'", "mensagem" : "' .$e->getMessage() .'", "arquivo:" : "' .$e->getFile() .'", "linha:" : "' .$e->getLine() .'"}';
		}
	}
	
	function estudo_list_removidos($params) {
		iniciaTransacao($params['hash']);
		
		try {
			$results = queryListResult('SELECT nrestudo FROM estudo WHERE dataexclusao IS NOT NULL', null);
			
			$response = '{"response" : "OK", "itens" : [';
			
			foreach($results as $result) {
				$response = $response .'{';

				foreach($result as $key=>$val) {
					if (!is_numeric ( $key )) {
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
	
	function estudo_remove($params) {
		iniciaTransacao($params['hash']);
		
		try {
			createQuery('UPDATE estudo SET dataexclusao = :data WHERE nrestudo = :nrestudo', array("data"=>date('Y-m-d H:i:s'), "nrestudo"=>$params['id']));
			createQuery('DELETE FROM estudo WHERE dataexclusao IS NOT NULL AND DATEDIFF(NOW(), dataexclusao) >= 730', null);
			
			echo '{"response":"OK"}';
			
		} catch(Exception $e) {
			echo '{"response" : "ERRO", "codigo" : "' .$e->getCode() .'", "mensagem" : "' .$e->getMessage() .'", "arquivo:" : "' .$e->getFile() .'", "linha:" : "' .$e->getLine() .'"}';
		}
	}
?>