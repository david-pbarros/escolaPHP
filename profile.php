<?php header ('Content-type: text/html; charset=UTF-8'); 
	require_once 'DAO.php';
	require_once 'security.php';
	
	function obtemIdProfile($params) {
		return $result = querySingleResult('SELECT id FROM profile WHERE dataexclusao IS NULL AND nome = :nome', array("nome"=>urldecode($params['nome'])));
	}
	
	function profile_insert($params) {
		iniciaTransacao($params['hash']);
		
		try {
			$result = obtemIdProfile($params);
			
			if(!empty($result)) {
				echo '{"response" : "existente", "id_online" : ' .$result->id .'}';
			
			} else {
				$queryParam = array("id"=>obtemSequence('profile'), "dtultimaatualiza"=>date('Y-m-d H:i:s'), "nome"=>urldecode($params['nome']));
			
				createQuery('INSERT INTO profile(id, dtultimaatualiza, nome) VALUES (:id, :dtultimaatualiza, :nome)', $queryParam);
				
				$result = obtemIdProfile($params);
				
				echo '{"response" : "novo", "id_online" : ' .$result->id .'}';
			}
		} catch(Exception $e) {
			echo '{"response" : "ERRO", "codigo" : "' .$e->getCode() .'", "mensagem" : "' .$e->getMessage() .'", "arquivo:" : "' .$e->getFile() .'", "linha:" : "' .$e->getLine() .'"}';
		}
	}
	
	function profile_update($params) {
		iniciaTransacao($params['hash']);
		
		$queryParam = array("id"=>$params['id_online'], "nome"=>urldecode($params['nome']), "dtultimaatualiza"=>date('Y-m-d H:i:s'));
		
		try {
			createQuery('UPDATE profile SET nome = :nome, dtultimaatualiza = :dtultimaatualiza WHERE id = :id', $queryParam);
			
			echo '{"response":"OK"}';
			
		} catch(Exception $e) {
			echo '{"response" : "ERRO", "codigo" : "' .$e->getCode() .'", "mensagem" : "' .$e->getMessage() .'", "arquivo:" : "' .$e->getFile() .'", "linha:" : "' .$e->getLine() .'"}';
		}
	}
	
	function profile_list($params) {
		iniciaTransacao($params['hash']);
		
		try {
			$results = queryListResult('SELECT id, nome FROM profile WHERE dataexclusao IS NULL AND dtultimaatualiza > :data', array("data"=>getFormatedDate($params['data_ultima'])));
			
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
	
	function profile_list_removidos($params) {
		iniciaTransacao($params['hash']);
		
		try {
			$results = queryListResult('SELECT id WHERE dataexclusao IS NOT NULL', null);
			
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
	
	function profile_remove($params) {
		iniciaTransacao($params['hash']);
		
		try {
			createQuery('UPDATE profile SET dataexclusao = :data WHERE id = :id', array("data"=>date('Y-m-d H:i:s'), "id"=>$params['id']));
			createQuery('DELETE FROM profile WHERE dataexclusao IS NOT NULL AND DATEDIFF(NOW(), dataexclusao) >= 730', null);
		
			echo '{"response":"OK"}';
			
		} catch(Exception $e) {
			echo '{"response" : "ERRO", "codigo" : "' .$e->getCode() .'", "mensagem" : "' .$e->getMessage() .'", "arquivo:" : "' .$e->getFile() .'", "linha:" : "' .$e->getLine() .'"}';
		}
	}
?>