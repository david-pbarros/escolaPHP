<?php header ('Content-type: text/html; charset=UTF-8'); 
	require_once 'DAO.php';
	require_once 'security.php';
	
	function obtemIdAjudante($params) {
		return $result = querySingleResult('SELECT id FROM ajudante WHERE dataexclusao IS NULL AND nome = :nome', array("nome"=>urldecode($params['nome'])));
	}
	
	function ajudante_insert($params) {
		iniciaTransacao($params['hash']);
		
		try {
			$result = obtemIdAjudante($params);
			
			if(!empty($result)) {
				echo '{"response" : "existente", "id_online" : ' .$result->id .'}';
			
			} else {
				$data = null;
				
				if ($params['ultimadesignacao'] != '') {
					$data = getFormatedDate($params['ultimadesignacao']);
				}
			
				$queryParam = array("id"=>obtemSequence('ajudante'), "dtultimaatualiza"=>date('Y-m-d H:i:s'), "genero"=>$params['genero'], "nome"=>urldecode($params['nome']), "ultimadesignacao"=>$data);
			
				createQuery('INSERT INTO ajudante(id, dtultimaatualiza, genero, nome, ultimadesignacao) VALUES (:id, :dtultimaatualiza, :genero, :nome, :ultimadesignacao)', $queryParam);
				
				$result = obtemIdAjudante($params);
				
				echo '{"response" : "novo", "id_online" : ' .$result->id .'}';
			}
		} catch(Exception $e) {
			echo '{"response" : "ERRO", "codigo" : "' .$e->getCode() .'", "mensagem" : "' .$e->getMessage() .'", "arquivo:" : "' .$e->getFile() .'", "linha:" : "' .$e->getLine() .'"}';
		}
	}
	
	function ajudante_update($params) {
		iniciaTransacao($params['hash']);
		
		$data = null;
		
		if (array_key_exists('ultimadesignacao', $params)) {
			$data = getFormatedDate($params['ultimadesignacao']);
		}
				
		$queryParam = array("id"=>$params['id_online'], "nome"=>urldecode($params['nome']), "ultimadesignacao"=>$data, "dtultimaatualiza"=>date('Y-m-d H:i:s'));
		
		try {
			createQuery('UPDATE ajudante SET nome = :nome, ultimadesignacao = :ultimadesignacao, dtultimaatualiza = :dtultimaatualiza WHERE id = :id', $queryParam);
		
			echo '{"response":"OK"}';
			
		} catch(Exception $e) {
			echo '{"response" : "ERRO", "codigo" : "' .$e->getCode() .'", "mensagem" : "' .$e->getMessage() .'", "arquivo:" : "' .$e->getFile() .'", "linha:" : "' .$e->getLine() .'"}';
		}
	}
	
	function ajudante_list($params) {
		iniciaTransacao($params['hash']);
		
		$nome = "";
		
		try {
			$results = queryListResult('SELECT id, genero, nome, ultimadesignacao FROM ajudante WHERE dataexclusao IS NULL AND dtultimaatualiza > :data', array("data"=>getFormatedDateTime($params['data_ultima'])));
			
			$response = '{"response" : "OK", "itens" : [';
			
			foreach($results as $result) {
				$nome = $result['nome'];
				$ultimaD = null;
				
				$response = $response .'{';
				foreach($result as $key=>$val) {
					if (!is_numeric ( $key )) {
						if ($key == 'ultimadesignacao') {
							if ($val != NULL) {
								$val = strtotime($val );
								$val   = '"' .date('d/m/Y H:i:s',$val) .'"';
							
							} else {
								$val = '""';
							}
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
	function ajudante_list_removidos($params) {
		iniciaTransacao($params['hash']);
		
		try {
			$results = queryListResult('SELECT id FROM ajudante WHERE dataexclusao IS NOT NULL', null);
			
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
			'{"response" : "ERRO", "codigo" : "' .$e->getCode() .'", "mensagem" : "' .$e->getMessage() .'", "arquivo:" : "' .$e->getFile() .'", "linha:" : "' .$e->getLine() .'"}';
		}
	}
	
	function ajudante_remove($params) {
		iniciaTransacao($params['hash']);
		
		try {
			createQuery('UPDATE ajudante SET dataexclusao = :data WHERE id = :id', array("data"=>date('Y-m-d H:i:s'), "id"=>$params['id']));
			createQuery('DELETE FROM ajudante WHERE dataexclusao IS NOT NULL AND DATEDIFF(NOW(), dataexclusao) >= 730', null);
		
			echo '{"response":"OK"}';
			
		} catch(Exception $e) {
			'{"response" : "ERRO", "codigo" : "' .$e->getCode() .'", "mensagem" : "' .$e->getMessage() .'", "arquivo:" : "' .$e->getFile() .'", "linha:" : "' .$e->getLine() .'"}';
		}
	}
?>