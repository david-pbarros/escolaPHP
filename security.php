<?php header ('Content-type: text/html; charset=UTF-8'); 
	set_include_path('phpseclib' .PATH_SEPARATOR .get_include_path() );

	require_once 'DAO.php';
	require_once 'Crypt/RSA.php';
	
	$pieces;
	
	function decript($hash) {
		$encPpKey = 'LS0tLS1CRUdJTiBSU0EgUFJJVkFURSBLRVktLS0tLQ0KTUlJQ1hRSUJBQUtCZ1FDM3BtVU1VUS80NG83eHZMMkhSaFZQLzJxVkEvTkRCRGZGdENrbFJldU1iTGNRa1k1Ug0KVWpTTkloUFl2UWk3dXd0bnY1R1ZnVForUGt5TnlSZ092dS9MaWErK24yeFJLMDhma05xdkxNR2trZFg0VWo5Qw0KRXlTaGw0QUZFdkJ5WkNOMWI5TnZwZVZXMnl2Zjl5SXV5dW1SNXZKOGxMbXVPSXZQZmpHTkkvUkJQd0lEQVFBQg0KQW9HQWYzaUdjTnNmUEFCOWVYc3BEbWp0eUI0Z0s1aVhVKy9zaWxTM3JvQnVzNFNPT0hqZmtNQi9hMnE0M2Rxdg0KNGlZOUQyRWZ1dWI2SFB3L0JMY005TWRCQnhiZDZQZ2ZUd2NCY1c4aElPOWxBVW5STjlHWlNaVEFpUTBlUTNEcg0KTUd6WmpKL2o1U1dKVWZubDdiaiswVE9EN1MxY3FnNzEwYlFab3FSWWlOMjZMTmtDUVFEcmZSaHQyeHFVKzd2NQ0KcHNmRlJ0MEFZY0lmRmJpTmJZSFF5d1Y0bGNPUXRKTldmcUVxRHJhN1VxWmdEd3JHZHNub05YYlR0N2I2M0N4Ng0KN3hwMzBsYnJBa0VBeDZWbnZoTEV1YUdwcHhlaFBCUXdFS0JyYkNBbUxQdHd0MXBReklLN3lyL3JmcXE5Nnp5Yg0Kd0J3c3MvczlGTmpNQVlxNExBOGk5cG1xNUVSQ1JieFIvUUpCQU11K0dlckNUUWRsbmNkc0V4K09KaHYwZUwzbw0KVHhxZUNsa1pyb3djRjI0VnJmeUI1dks2ZEVNeVNSeUhKeTE3RFVuSktCd1pzVWp1UWRYREZjVmh5UzBDUUdObA0KNmFuTGhHQjdxWkRFaGdUNGRCbkRGTmluaFBvK1VaY29BelJmSG9wS1ZVQWlXQjRuZGRBRzl3YkEzbDlqdE9aTA0KbjNob0xOc2tGTjVEVWMrUWZDMENRUUNuL2RHd091clJqdkVHV0Y3dE1qR2lRQ3Iwa3BOeUJjZlVzZ3pFc2ZaNA0KbmNWTFEwekxYcWhvNzBPTFFKMmpBbTVEMTV1R0kzbU1vekVPeGJEbzEzbFQNCi0tLS0tRU5EIFJTQSBQUklWQVRFIEtFWS0tLS0t';
	
		$ciphertext = base64_decode($hash);
	
		$pivKey = base64_decode($encPpKey);
		
		$rsa = new Crypt_RSA();
		$rsa->setEncryptionMode(CRYPT_RSA_ENCRYPTION_PKCS1);
		
		$rsa->loadKey($pivKey);
			
		return $rsa->decrypt($ciphertext);
	}
	
	function iniciaTransacaoNaoSegura($hash) {
		global $app;
		global $pieces;
		
		$deCipherText = decript($hash);
		
		$pieces = explode(';', $deCipherText);
		
		//echo  var_dump($pieces);
		
		createConnection($pieces[0]);
		
		if ($pieces[3] < 400) {
			echo '{"response" : "ERRO", "mensagem" : "Versão do software inválida. Atualizar para a versão mais recente"}';
			return false;
		}
		
		return true;
	}
	
	
	function iniciaTransacao($hash) {
		global $app;
		global $pieces;
		
		if (iniciaTransacaoNaoSegura($hash)) {
			$stmt = createQuery('SELECT 1 FROM usuario WHERE nome = :nome  AND senha = :senha', array("nome"=>$pieces[1], "senha"=>$pieces[2]));
			
			if ($stmt->rowCount() != 1) {
				$app->halt(401, 'Erro na autenticação!');
			}
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