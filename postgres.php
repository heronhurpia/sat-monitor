<?php
if(!@($conexao=pg_connect ("host=127.0.0.1 dbname=sat port=5432 user=sat_homolog password=U3t8QYg"))) {
   print "Não foi possível estabelecer uma conexão com o banco de dados.\n";
} else {
   pg_close ($conexao);
   print "Conexão OK!\n"; 
}
?>
