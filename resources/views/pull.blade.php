<?php 
	echo shell_exec('git pull');
	echo shell_exec('php artisan migrate') ;
	
	echo '<p><h5>Executado GIT pull!</h5></p>';
	echo '<p><a href="lista">Clique aqui para continuar</a></p>';

?>
