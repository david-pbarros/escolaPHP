<?php header ('Content-type: text/html; charset=UTF-8'); 
	set_include_path('phpseclib' .PATH_SEPARATOR .get_include_path() );

	require_once 'DAO.php';
	require_once 'Crypt/RSA.php';
	
?>