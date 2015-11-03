<?php header ('Content-type: text/html; charset=UTF-8'); 
	ini_set('display_errors', 'On');
	error_reporting(E_ALL | E_STRICT);
	
	date_default_timezone_set('America/Sao_Paulo');

	require_once 'Slim/Slim.php';
	require_once 'DAO.php';
	require_once 'security.php';
	require_once 'mes.php';
	require_once 'ajudante.php';
	require_once 'estudante.php';
	require_once 'estudo.php';
	require_once 'profile.php';
	require_once 'item_profile.php';
	require_once 'usuario.php';
	require_once 'semana.php';
	require_once 'designacao.php';
	
	\Slim\Slim::registerAutoloader();
	
	$app = new \Slim\Slim();
	
	$app->get('/', function () {
		echo "Escola WebService Host.";
	});
	
	$app->post('/logon', function() {
		global $app;
		
		iniciaTransacao($app->request()->params('hash'));
		echo '{"response" : "OK"}';
	});
	
	$app->get('/versao', function() {
		global $app;
		
		iniciaTransacao($app->request()->params('hash'));
		echo '{"versao" : 400, "msg" : "Acesse: http://escolaministerio.jwdbcorp.dx.am/download.php e clique no link Atualização'
                                                .'\nPrincipais alterações da versão:\n- Deleção mantendo histórico '
                                                .'de \'Estudos\', \'Ajudantes\' e \'Estudantes\'."}';
	});
	
	$app->get('/lastSinc', function() {
		global $app;
		iniciaTransacaoNaoSegura($app->request()->params('hash'));
		
		$result = querySingleResult("SELECT count(*) qtd FROM sincronismo WHERE situacao = 'C'", null);
		
		if(!empty($result) && $result->qtd > 0) {
			$result = querySingleResult("SELECT MAX(data) data FROM sincronismo WHERE situacao = 'C'", null);
			
			if(!empty($result)) {
				$val = strtotime($result->data);
				$val   = '"' .date('d/m/Y H:i:s',$val) .'"';

				echo '{"response" : "existente", "data" : ' .$val .'}';
			}
		} else {
			echo '{"response" : "inexistente"}';
		}
	});
	
	$app->post('/lastSinc', function() {
		global $app;
		global $pieces;
		
		iniciaTransacao($app->request()->params('hash'));
		
		$queryParam = array("data"=>date('Y-m-d H:i:s'), "usuario"=>$pieces[1], "situacao"=>$app->request()->params('status'));
			
		createQuery('INSERT INTO sincronismo(data, usuario, situacao) VALUES (:data, :usuario, :situacao)', $queryParam);
	});
	
	//funcoes para o mes
	$app->get('/mes', function() {
		global $app;
		mes_list($app->request()->params());
	});
	
	$app->post('/mes', function() {
		global $app;
		mes_insert($app->request()->params());
	});
	
	$app->put('/mes', function() {
		global $app;
		mes_update($app->request()->params());
	});
	
	//funcoes para o ajudante
	$app->get('/ajudante', function() {
		global $app;
		ajudante_list($app->request()->params());
	});
	
	$app->get('/ajudante/removidos', function() {
		global $app;
		ajudante_list_removidos($app->request()->params());
	});
	
	$app->post('/ajudante', function() {
		global $app;
		ajudante_insert($app->request()->params());
	});
	
	$app->put('/ajudante', function() {
		global $app;
		ajudante_update($app->request()->params());
	});
	
	$app->delete('/ajudante', function() {
		global $app;
		ajudante_remove($app->request()->params());
	});
	
	//funcoes para o estudante
	$app->get('/estudante', function() {
		global $app;
		estudante_list($app->request()->params());
	});
	
	$app->get('/estudante/removidos', function() {
		global $app;
		estudante_list_removidos($app->request()->params());
	});
	
	$app->post('/estudante', function() {
		global $app;
		estudante_insert($app->request()->params());
	});
	
	$app->put('/estudante', function() {
		global $app;
		estudante_update($app->request()->params());
	});
	
	$app->delete('/estudante', function() {
		global $app;
		estudante_remove($app->request()->params());
	});
	
	//funcoes para o estudo
	$app->get('/estudo', function() {
		global $app;
		estudo_list($app->request()->params());
	});
	
	$app->get('/estudo/removidos', function() {
		global $app;
		estudo_list_removidos($app->request()->params());
	});
	
	$app->post('/estudo', function() {
		global $app;
		estudo_insert($app->request()->params());
	});
	
	$app->delete('/estudo', function() {
		global $app;
		estudo_remove($app->request()->params());
	});
	
	//funcoes para o profile
	$app->get('/profile', function() {
		global $app;
		profile_list($app->request()->params());
	});
	
	$app->get('/profile/removidos', function() {
		global $app;
		profile_list_removidos($app->request()->params());
	});
	
	$app->post('/profile', function() {
		global $app;
		profile_insert($app->request()->params());
	});
	
	$app->put('/profile', function() {
		global $app;
		profile_update($app->request()->params());
	});
	
	$app->delete('/profile', function() {
		global $app;
		profile_remove($app->request()->params());
	});
	
	//funcoes para o item profile
	$app->get('/itemProfile', function() {
		global $app;
		itemProfile_list($app->request()->params());
	});
	
	$app->get('/itemProfile/removidos', function() {
		global $app;
		itemProfile_list_removidos($app->request()->params());
	});
	
	$app->post('/itemProfile', function() {
		global $app;
		itemProfile_insert($app->request()->params());
	});
	
	$app->delete('/itemProfile', function() {
		global $app;
		itemProfile_remove($app->request()->params());
	});
	
	//funcoes para o usuario
	$app->get('/usuario', function() {
		global $app;
		usuario_list($app->request()->params());
	});
	
	$app->get('/usuario/removidos', function() {
		global $app;
		usuario_list_removidos($app->request()->params());
	});
	
	$app->post('/usuario', function() {
		global $app;
		usuario_insert($app->request()->params());
	});
	
	$app->put('/usuario', function() {
		global $app;
		usuario_update($app->request()->params());
	});
	
	$app->delete('/usuario', function() {
		global $app;
		usuario_remove($app->request()->params());
	});
	
	//funcoes para o semana
	$app->get('/semana', function() {
		global $app;
		semana_list($app->request()->params());
	});
	
	$app->post('/semana', function() {
		global $app;
		semana_insert($app->request()->params());
	});
	
	$app->put('/semana', function() {
		global $app;
		semana_update($app->request()->params());
	});
	
	//funcoes para a designacao
	$app->get('/designacao', function() {
		global $app;
		designacao_list($app->request()->params());
	});
	
	$app->get('/designacao/removidos', function() {
		global $app;
		designacao_list_removidos($app->request()->params());
	});
	
	$app->post('/designacao', function() {
		global $app;
		designacao_insert($app->request()->params());
	});
	
	$app->put('/designacao', function() {
		global $app;
		designacao_update($app->request()->params());
	});
	
	$app->delete('/designacao', function() {
		global $app;
		designacao_remove($app->request()->params());
	});
	
	$app->post('/pass', function() {
		global $app;
		trocaSenha($app->request()->params());
	});
	
	$app->run();
?>