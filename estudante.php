<?php header ('Content-type: text/html; charset=UTF-8'); 
	require_once 'DAO.php';
	require_once 'security.php';
	
	function obtemIdEstudante($params) {
		return $result = querySingleResult('SELECT id FROM estudante WHERE dataexclusao IS NULL AND nome = :nome', array("nome"=>urldecode($params['nome'])));
	}
	
	function estudante_insert($params) {
		iniciaTransacao($params['hash']);
		
		try {
			$result = obtemIdEstudante($params);
			
			if(!empty($result)) {
				echo '{"response" : "existente", "id_online" : ' .$result->id .'}';
			
			} else {
				$data = null;
				
				if ($params['ultimadesignacao'] != '') {
					$data = getFormatedDate($params['ultimadesignacao']);
				}
				
				$queryParam = array("id"=>obtemSequence('estudante'), "dtultimaatualiza"=>date('Y-m-d H:i:s'), "genero"=>$params['genero'], "nome"=>urldecode($params['nome']), "ultimadesignacao"=>$data,
									"desabilitado"=>$params['desabilitado'], "naoajudante"=>$params['naoAjudante'], "observacao"=>$params['observacao'], "salaultimadesignacao"=>$params['salaUltima'],
									"exclusaologica"=>$params['excluidoLogico']);
			
				createQuery('INSERT INTO estudante(id, desabilitado, dtultimaatualiza, genero, naoajudante, nome, observacao, salaultimadesignacao, ultimadesignacao, exclusaologica) VALUES (:id, :desabilitado, :dtultimaatualiza, :genero, :naoajudante, :nome, :observacao, :salaultimadesignacao, :ultimadesignacao, :exclusaologica)', $queryParam);
				
				$result = obtemIdEstudante($params);
				
				echo '{"response" : "novo", "id_online" : ' .$result->id .'}';
			}
		} catch(Exception $e) {
			echo '{"response" : "ERRO", "codigo" : "' .$e->getCode() .'", "mensagem" : "' .$e->getMessage() .'", "arquivo:" : "' .$e->getFile() .'", "linha:" : "' .$e->getLine() .'"}';
		}
	}
	
	function estudante_update($params) {
		iniciaTransacao($params['hash']);
		
		$data = null;
		
		if (array_key_exists('ultimadesignacao', $params)) {
			$data = getFormatedDate($params['ultimadesignacao']);
		}
				
		$queryParam = array("id"=>$params['id_online'], "nome"=>urldecode($params['nome']), "ultimadesignacao"=>$data, "desabilitado"=>$params['desabilitado'],
							"naoajudante"=>$params['naoAjudante'], "observacao"=>$params['observacao'], "salaultimadesignacao"=>$params['salaUltima'], 
							"dtultimaatualiza"=>date('Y-m-d H:i:s'), "exclusaologica"=>$params['excluidoLogico']);
		
		try {
			createQuery('UPDATE estudante SET nome = :nome, ultimadesignacao = :ultimadesignacao, desabilitado = :desabilitado, naoajudante = :naoajudante,'
							.' observacao = :observacao, salaultimadesignacao = :salaultimadesignacao, exclusaologica = :exclusaologica, dtultimaatualiza = :dtultimaatualiza WHERE id = :id', $queryParam);
			
			echo '{"response":"OK"}';
			
		} catch(Exception $e) {
			echo '{"response" : "ERRO", "codigo" : "' .$e->getCode() .'", "mensagem" : "' .$e->getMessage() .'", "arquivo:" : "' .$e->getFile() .'", "linha:" : "' .$e->getLine() .'"}';
		}
	}
	
	function estudante_list($params) {
		iniciaTransacao($params['hash']);
		
		$nome = "";
		
		try {
			$results = queryListResult('SELECT id, desabilitado, genero, naoajudante, nome, observacao, salaultimadesignacao, ultimadesignacao FROM estudante WHERE dataexclusao IS NULL AND dtultimaatualiza > :data', array("data"=>getFormatedDateTime($params['data_ultima'])));
			
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
								$val   = '"' .date('d/m/Y',$val) .'"';
							
							} else {
								$val = '""';
							}
						} else if ($key == 'salaultimadesignacao' &&  $val == NULL) {
							$val = '""';
						
						} else if ($key == 'nome' ) {
							$val = urlencode($val);
						
                                                } else {
                                                   if ($val == NULL || $val == 'null') {
                                                           $val = '""';
                                                   } else {
                                                           $val = '"' .$val .'"';
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
	
	function estudante_list_removidos($params) {
		iniciaTransacao($params['hash']);
		
		try {
			$results = queryListResult('SELECT id FROM estudante WHERE dataexclusao IS NOT NULL', null);
			
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
	
	function estudante_remove($params) {
		iniciaTransacao($params['hash']);
		
		try {
			createQuery('UPDATE estudante SET dataexclusao = :data WHERE id = :id', array("data"=>date('Y-m-d H:i:s'), "id"=>$params['id']));
			createQuery('DELETE FROM estudante WHERE dataexclusao IS NOT NULL AND DATEDIFF(NOW(), dataexclusao) >= 730', null);
		
			echo '{"response":"OK"}';
			
		} catch(Exception $e) {
			echo '{"response" : "ERRO", "codigo" : "' .$e->getCode() .'", "mensagem" : "' .$e->getMessage() .'", "arquivo:" : "' .$e->getFile() .'", "linha:" : "' .$e->getLine() .'"}';
		}
	}
?>