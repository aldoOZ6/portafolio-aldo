<?php
$host="localhost";
$usuario="root";
$password="";
$bd="activo_fijo";
$puerto=3307;

try{
$pdo=new PDO("mysql:host=$host;port=$puerto;dbname=$bd;charset=utf8",$usuario,$password);
$pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

if(isset($_GET['id'])){
$id=$_GET['id'];
$pdo->prepare("DELETE FROM documentos_tractos WHERE id_tracto=?")->execute([$id]);
$pdo->prepare("DELETE FROM tractos WHERE id_tracto=?")->execute([$id]);
}

header("Location: tractos.php?msg=eliminado");
exit;

}catch(PDOException $e){
die($e->getMessage());
}
