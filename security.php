<?php header ('Content-type: text/html; charset=UTF-8'); 
	set_include_path('phpseclib' .PATH_SEPARATOR .get_include_path() );

	require_once 'DAO.php';
	require_once 'Crypt/RSA.php';
	
	function decript($hash) {
		$encPpKey = 'INFORMAR AQUI';
	
		$ciphertext = base64_decode($hash);
	
		$pivKey = base64_decode($encPpKey);
		
		$rsa = new Crypt_RSA();
		$rsa->setEncryptionMode(CRYPT_RSA_ENCRYPTION_PKCS1);
		
		$rsa->loadKey($pivKey);
			
		return $rsa->decrypt($ciphertext);
	}
	
	function iniciaTransacao($hash) {
		global $app;
		
		$deCipherText = decript($hash);
		
		$pieces = explode(';', $deCipherText);
		
		//echo  var_dump($pieces);
		
		createConnection($pieces[0]);
		
		$stmt = createQuery('SELECT 1 FROM usuario WHERE nome = :nome  AND senha = :senha', array("nome"=>$pieces[1], "senha"=>$pieces[2]));
		
		if ($stmt->rowCount() != 1) {
			$app->halt(401, 'Erro na autenticação!');
		}
	}
        
        function trocaSenha($params) {    
                iniciaTransacao($params['hash']);
              
                $queryParam = array("id"=>$params['id_online'], "senha"=>utf8_encode($params['senha']), "reiniciaSenha"=>$params['reiniciaSenha'], "dtultimaatualiza"=>date('Y-m-d H:i:s'));
		
		try {
                        createQuery('UPDATE usuario SET senha = :senha, reiniciaSenha = :reiniciaSenha, dtultimaatualiza = :dtultimaatualiza WHERE id = :id', $queryParam);
			
			echo '{"response":"OK"}';
			
		} catch(Exception $e) {
                
			echo '{"response" : "ERRO", "codigo" : "' .$e->getCode() .'", "mensagem" : "' .$e->getMessage() .'", "arquivo:" : "' .$e->getFile() .'", "linha:" : "' .$e->getLine() .'"}';
		}
        }
?>