<?php header ('Content-type: text/html; charset=UTF-8'); 
	$dbh = null;

	function createConnection($dbName) {
		global $dbh;
		global $app;
		
		
                try {	
                        if ($dbName == '11111') {
                                 $dbh = new PDO('mysql:host=fdb3.awardspace.net;dbname=1916634_'.$dbName .';charset=utf8', '1916634_'.$dbName, 'escola'.$dbName, array(PDO::ATTR_PERSISTENT=>true,PDO::MYSQL_ATTR_INIT_COMMAND =>"SET NAMES utf8"));
                       
                       } else {
                                $dbh = new PDO('mysql:host=fdb3.awardspace.net;dbname=1912816_'.$dbName .';charset=utf8', '1912816_'.$dbName, 'escola'.$dbName, array(PDO::ATTR_PERSISTENT=>true));
                        }    
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
	
	function getFormatedDate($dataParam) {
		$d = date_create_from_format('d/m/Y H:i:s', $dataParam);
				
		return $d->format('Y-m-d H:i:s');
	}
?>