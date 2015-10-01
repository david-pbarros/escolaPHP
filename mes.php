<?php header ('Content-type: text/html; charset=UTF-8'); 
	require_once 'DAO.php';
	require_once 'security.php';
	
	function obtemIdMes($params) {
		return $result = querySingleResult('SELECT id FROM mesdesignacao WHERE ano = :ano  AND mes = :mes', array("ano"=>$params['ano'], "mes"=>$params['mes']));
	}
	
	function mes_insert($params) {
		iniciaTransacao($params['hash']);
		
		try {
			$result = obtemIdMes($params);
			
			if(!empty($result)) {
				echo '{"response" : "existente", "id_online" : ' .$result->id .'}';
			
			} else {
				$queryParam = array("id"=>obtemSequence('mesdesignacao'), "dtultimaatualiza"=>date('Y-m-d H:i:s'), "ano"=>$params['ano'], "mes"=>$params['mes'], "status"=>$params['status']);
			
				createQuery('INSERT INTO mesdesignacao(id, ano, dtultimaatualiza, mes, status) VALUES (:id, :ano, :dtultimaatualiza, :mes, :status)', $queryParam);
				
				$result = obtemIdMes($params);
				
				echo '{"response" : "novo", "id_online" : ' .$result->id .'}';
			}
		} catch(Exception $e) {
			echo '{"response" : "ERRO", "codigo" : "' .$e->getCode() .'", "mensagem" : "' .$e->getMessage() .'", "arquivo:" : "' .$e->getFile() .'", "linha:" : "' .$e->getLine() .'"}';
		}
	}
	
	function mes_update($params) {
		iniciaTransacao($params['hash']);
		
		$queryParam = array("id"=>$params['id'], "status"=>$params['status'], "dtultimaatualiza"=>date('Y-m-d H:i:s'));
		
		try {
			createQuery('UPDATE mesdesignacao SET status = :status, dtultimaatualiza = :dtultimaatualiza WHERE id = :id', $queryParam);
			echo '{"response":"OK"}';
			
		} catch(Exception $e) {
			echo '{"response" : "ERRO", "codigo" : "' .$e->getCode() .'", "mensagem" : "' .$e->getMessage() .'", "arquivo:" : "' .$e->getFile() .'", "linha:" : "' .$e->getLine() .'"}';
		}
	}
	
	function mes_list($params) {
		iniciaTransacao($params['hash']);
		
		try {
			$results = queryListResult('SELECT id, ano, mes, status FROM mesdesignacao WHERE dtultimaatualiza > :data', array("data"=>getFormatedDate($params['data_ultima'])));
			
			$response = '{"response" : "OK", "itens" : [';
			
			foreach($results as $result) {
				$response = $response .'{"id" : '.$result['id'] .', "ano" : ' .$result['ano'] .', "mes" : ' .$result['mes']  .', "status" : ' .$result['status'] .'},';
			}
			
			echo rtrim($response, ",") .']}';
			
		} catch(Exception $e) {
			echo '{"response" : "ERRO", "codigo" : "' .$e->getCode() .'", "mensagem" : "' .$e->getMessage() .'", "arquivo:" : "' .$e->getFile() .'", "linha:" : "' .$e->getLine() .'"}';
		}
	}
?>