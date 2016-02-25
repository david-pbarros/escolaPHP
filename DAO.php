<?php header ('Content-type: text/html; charset=UTF-8'); 
	$dbh = null;

	function createConnection($dbName) {
		global $dbh;
		global $app;
		
		$cong = $dbName; 
		
                try {	
                	$cong = $dbName;
                    $host;
                    
                    if ($cong == '48496' || $cong == '8771') {
						$dbName = '1912816_'.$cong;
		                $host = 'pdb19.awardspace.net';
					
					} elseif ($cong == '11111') {
						$dbName = '1916634_' .$cong;
		                $host = 'fdb3.awardspace.net;dbname';
					}
					
					$dbh = new PDO('mysql:host=' .$host .';dbname='.$dbName .';charset=utf8', $dbName, 'escola'.$cong, array(PDO::ATTR_PERSISTENT=>true));
					   
					//localHost
					//$dbh = new PDO('mysql:host=localhost;dbname=escola_48496', 'root');
                } catch (PDOException $e) {
                        $app->halt(400, 'Erro ao conectar ao banco de dados' .' Mensagem: ' .$e->getMessage());
                }
	}
	
	function createQuery($query, $parameters) {
		global $dbh;
                global $app;
		
		if (!isset($dbh)) {
			$app->halt(400, 'Sem conexão com o banco.');
		}
		
		$stmt = $dbh->prepare($query);
		if (isset($parameters)) {
			$stmt->execute($parameters);
		
		} else {
			$stmt->execute();
		}
		
		return $stmt;
	}
	
	function querySingleResult($query, $parameters) {
		$stmt = createQuery($query, $parameters);
		$result = $stmt->fetch(PDO::FETCH_OBJ);
                $stmt->closeCursor();
                
                return $result;
	}
	
	function queryListResult($query, $parameters) {
		$stmt = createQuery($query, $parameters);
                $list = $stmt->fetchAll();
                $stmt->closeCursor();
                
                return $list;
	}
	
	function obtemSequence($tabela) {
		$result = querySingleResult('SELECT sequencia FROM sequencia WHERE tabela = :tabela', array("tabela"=>$tabela));
		$seq = $result->sequencia;
		
		createQuery('UPDATE sequencia SET sequencia = :sequencia WHERE tabela = :tabela', array("tabela"=>$tabela, "sequencia"=>$seq+1));

		return $tabela .'_' .$seq;
	}
	
	function getFormatedDateTime($dataParam) {
		$d = date_create_from_format('d/m/Y H:i:s', $dataParam);
				
		return $d->format('Y-m-d H:i:s');
	}
	
	function getFormatedDate($dataParam) {
		$d = date_create_from_format('d/m/Y', $dataParam);
				
		return $d->format('Y-m-d');
	}
?>