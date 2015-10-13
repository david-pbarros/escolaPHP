<?php header ('Content-type: text/html; charset=UTF-8'); 
	require_once 'DAO.php';
	require_once 'security.php';
	
	function obtemIdUsuario($params) {
		return $result = querySingleResult('SELECT id FROM usuario WHERE dataexclusao IS NULL AND nome = :nome', array("nome"=>urldecode($params['nome'])));
	}
	
	function usuario_insert($params) {
		iniciaTransacao($params['hash']);
		
		try {
			$result = obtemIdUsuario($params);
			
			if(!empty($result)) {
				echo '{"response" : "existente", "id_online" : ' .$result->id .'}';
			
			} else {
				$queryParam = array("id"=>obtemSequence('usuario'), "dtultimaatualiza"=>date('Y-m-d H:i:s'), "senha"=>utf8_encode($params['senha']), "nome"=>urldecode($params['nome']),
									"bloqueado"=>$params['bloqueado'], "reiniciaSenha"=>$params['reiniciaSenha'], "profile_id"=>$params['profile']);
			
				createQuery('INSERT INTO usuario(id, dtultimaatualiza, senha, nome, reiniciaSenha, bloqueado, profile_id) VALUES (:id, :dtultimaatualiza, :senha, :nome, :reiniciaSenha, :bloqueado, :profile_id)', $queryParam);
				
				$result = obtemIdUsuario($params);
				
				echo '{"response" : "novo", "id_online" : ' .$result->id .'}';
			}
		} catch(Exception $e) {
			echo '{"response" : "ERRO", "codigo" : "' .$e->getCode() .'", "mensagem" : "' .$e->getMessage() .'", "arquivo:" : "' .$e->getFile() .'", "linha:" : "' .$e->getLine() .'"}';
		}
	}
	
	function usuario_update($params) {
		iniciaTransacao($params['hash']);
		
		$queryParam = array("id"=>$params['id_online'], "senha"=>utf8_encode($params['senha']), "nome"=>urldecode($params['nome']), "dtultimaatualiza"=>date('Y-m-d H:i:s'),
									"bloqueado"=>$params['bloqueado'], "reiniciaSenha"=>$params['reiniciaSenha'], "profile_id"=>$params['profile']);
		
		try {
			createQuery('UPDATE usuario SET senha = :senha, nome = :nome, reiniciaSenha = :reiniciaSenha, bloqueado = :bloqueado, profile_id = :profile_id, dtultimaatualiza = :dtultimaatualiza WHERE id = :id', $queryParam);
			
			echo '{"response":"OK"}';
			
		} catch(Exception $e) {
			echo '{"response" : "ERRO", "codigo" : "' .$e->getCode() .'", "mensagem" : "' .$e->getMessage() .'", "arquivo:" : "' .$e->getFile() .'", "linha:" : "' .$e->getLine() .'"}';
		}
	}
	
	function usuario_list($params) {
		iniciaTransacao($params['hash']);
		
		$nome = "";
		
		try {
			$results = queryListResult('SELECT id, senha, nome, reiniciaSenha, bloqueado, profile_id FROM usuario WHERE dataexclusao IS NULL AND (dtultimaatualiza > :data OR dtultimaatualiza IS NULL)', array("data"=>getFormatedDateTime($params['data_ultima'])));
			
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
	
	function usuario_list_removidos($params) {
		iniciaTransacao($params['hash']);
		
		try {
			$results = queryListResult('SELECT id FROM usuario WHERE dataexclusao IS NOT NULL', null);
			
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
	
	function usuario_remove($params) {
		iniciaTransacao($params['hash']);
		
		try {
			createQuery('UPDATE usuario SET dataexclusao = :data WHERE id = :id', array("data"=>date('Y-m-d H:i:s'), "id"=>$params['id']));
			createQuery('DELETE FROM usuario WHERE dataexclusao IS NOT NULL AND DATEDIFF(NOW(), dataexclusao) >= 730', null);
		
			echo '{"response":"OK"}';
			
		} catch(Exception $e) {
			echo '{"response" : "ERRO", "codigo" : "' .$e->getCode() .'", "mensagem" : "' .$e->getMessage() .'", "arquivo:" : "' .$e->getFile() .'", "linha:" : "' .$e->getLine() .'"}';
		}
	}
?>