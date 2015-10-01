<?php header ('Content-type: text/html; charset=UTF-8'); 
	require_once 'DAO.php';
	require_once 'security.php';
	
	function obtemIdDesignacao($params) {
		return $result = querySingleResult('SELECT id FROM designacao WHERE dataexclusao IS NULL AND data = :data AND numero = :numero AND sala = :sala', array("data"=>getFormatedDate($params['data']), "numero"=>$params['numero'], "sala"=>$params['sala']));
	}
	
	function insereEstudoDesignacao($params, $id) {
		$queryParam = array("designacao"=>$id, "estudo"=>$params['estudo']);
				
		createQuery('INSERT INTO designacao_estudo(estudo_id, designacao_id) VALUES(:estudo, :designacao)', $queryParam);
	}
	
	function designacao_insert($params) {
		iniciaTransacao($params['hash']);
		
		try {
			$result = obtemIdDesignacao($params);
			
			if(!empty($result)) {
				echo '{"response" : "existente", "id_online" : ' .$result->id .'}';
			
			} else {
				$queryParam = array("id"=>obtemSequence('designacao'), "dtultimaatualiza"=>date('Y-m-d H:i:s'), "data"=>getFormatedDate($params['data']), "fonte"=>urldecode($params['fonte']),
									"numero"=>$params['numero'], "obsfolha"=>urldecode($params['ObsFolha']), "observacao"=>urldecode($params['observacao']), "sala"=>$params['sala'], "tema"=>urldecode($params['tema']),
									"ajudante_id"=>$params['ajudante'], "semana_id"=>$params['semana'], "estudante_id"=>$params['estudante'], "status"=>$params['status']);
			
				createQuery('INSERT INTO designacao(id, data, dtultimaatualiza, fonte, numero, obsfolha, observacao, sala, status, tema, ajudante_id, semana_id, estudante_id) VALUES (:id, :data, :dtultimaatualiza, :fonte, :numero, :obsfolha, :observacao, :sala, :status, :tema, :ajudante_id, :semana_id, :estudante_id)', $queryParam);
				
				$id = obtemIdDesignacao($params)->id;
				
				insereEstudoDesignacao($params, $id);
				
				echo '{"response" : "novo", "id_online" : ' .$id .'}';
			}
		} catch(Exception $e) {
			echo '{"response" : "ERRO", "mensagem" :' .$e->getMessage() .'}';
		}
	}
        
	function designacao_update($params) {
		iniciaTransacao($params['hash']);
		
                
		$queryParam = array("id"=>$params['id'], "dtultimaatualiza"=>date('Y-m-d H:i:s'), "fonte"=>urldecode($params['fonte']), "obsfolha"=>urldecode($params['ObsFolha']), "observacao"=>urldecode($params['observacao']),
							"tema"=>urldecode($params['tema']), "ajudante_id"=>$params['ajudante'], "estudante_id"=>$params['estudante'], "status"=>$params['status']);
		
		try {
			createQuery('UPDATE designacao SET  dtultimaatualiza = :dtultimaatualiza, fonte = :fonte, obsfolha = :obsfolha, observacao = :observacao, tema = :tema, ajudante_id = :ajudante_id, '
						.'estudante_id = :estudante_id, status = :status WHERE id = :id', $queryParam);
			
			$result = querySingleResult('SELECT 1 FROM designacao_estudo WHERE designacao_id = :designacao', array("designacao"=>$params['id']));
			
			if(!empty($result)) {
				createQuery('UPDATE designacao_estudo SET estudo_id = :estudo WHERE designacao_id = :id', array("estudo"=>$params['estudo'], "id"=>$params['id']));
			
			} else {
				insereEstudoDesignacao($params, $params['id']);
			}
			
			echo '{"response":"OK"}';
			
		} catch(Exception $e) {
			echo '{"response" : "ERRO", "mensagem" :' .$e->getMessage() .'}';
		}
	}
	
	function designacao_list($params) {
		iniciaTransacao($params['hash']);
		
		try {
			$results = queryListResult('SELECT id, data, fonte, numero, obsfolha, observacao, sala, status, tema, ajudante_id, semana_id, estudante_id, e.estudo_id FROM designacao d '
										.'LEFT JOIN designacao_estudo e ON e.designacao_id = d.id WHERE dataexclusao IS NULL AND dtultimaatualiza > :data', array("data"=>getFormatedDate($params['data_ultima'])));
			
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
						} else if (($key == 'ajudante_id' || $key == 'estudante_id') && ($val == '' || $val == NULL || $val == 'null')) {
							$val = '""';
                                                        
						} else if ($key == 'fonte' || $key == 'tema' || $key == 'obsfolha' || $key == 'observacao') {
                                                        if ($val == '' || $val == NULL || $val == 'null') {
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
			echo '{"response" : "ERRO", "mensagem" :' .$e->getMessage() .'}';
		}
	}
	
	function designacao_list_removidos($params) {
		iniciaTransacao($params['hash']);
		
		try {
			$results = queryListResult('SELECT id FROM designacao WHERE dataexclusao IS NOT NULL', null);
			
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
			echo '{"response" : "ERRO", "mensagem" :' .$e->getMessage() .'}';
		}
	}
	
	function designacao_remove($params) {
		iniciaTransacao($params['hash']);
		
		try {
			createQuery('DELETE FROM designacao_estudo WHERE designacao_id = :id', array("id"=>$params['id']));
			createQuery('UPDATE designacao SET dataexclusao = :data WHERE id = :id', array("data"=>date('Y-m-d H:i:s'), "id"=>$params['id']));
			
			createQuery('DELETE FROM designacao WHERE dataexclusao IS NOT NULL AND DATEDIFF(NOW(), dataexclusao) >= 730', null);
		
			echo '{"response":"OK"}';
			
		} catch(Exception $e) {
			echo '{"response" : "ERRO", "mensagem" :' .$e->getMessage() .'}';
		}
	}
?>