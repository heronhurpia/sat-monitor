<?php 
	echo "<pre>" . shell_exec('git pull') . "</pre>" ;
	echo "<pre>" . shell_exec('cd /app; php artisan migrate') . "</pre>" ;
	
	echo '<p><h5>Executado GIT pull!</h5></p>';
	echo '<p><a href="lista">Clique aqui para continuar</a></p>';

?>
