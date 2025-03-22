<?php include "../inc/dbinfo.inc"; ?>

<html>
<head>
  <title>Gestão de Receitas de Colaboradores</title>
</head>
<body>
<h1>Cadastro de Colaboradores</h1>

<?php
/* Conexão com o banco */
$constring = "host=" . DB_SERVER . " dbname=" . DB_DATABASE . " user=" . DB_USERNAME . " password=" . DB_PASSWORD ;
$connection = pg_connect($constring);

if (!$connection){
 echo "Erro ao conectar no banco de dados.";
 exit;
}

/* Verificar ou criar tabelas */
VerifyEmployeesTable($connection, DB_DATABASE);
VerifyFavoriteFoodsTable($connection, DB_DATABASE);

/* Cadastro de funcionário */
$employee_name = htmlentities($_POST['NAME']);
$employee_address = htmlentities($_POST['ADDRESS']);

if (strlen($employee_name) || strlen($employee_address)) {
  AddEmployee($connection, $employee_name, $employee_address);
}

/* Cadastro de comida preferida + tempo e data */
$food_employee_name = htmlentities($_POST['FOOD_NAME']);
$favorite_food = htmlentities($_POST['FAVORITE_FOOD']);
$prep_time = htmlentities($_POST['PREP_TIME']);
$created_at = htmlentities($_POST['CREATED_AT']);

if (strlen($food_employee_name) && strlen($favorite_food) && is_numeric($prep_time) && strlen($created_at)) {
  AddFavoriteFood($connection, $food_employee_name, $favorite_food, $prep_time, $created_at);
}
?>

<!-- Formulário de Funcionários -->
<h2>Adicionar Funcionário</h2>
<form action="<?PHP echo $_SERVER['SCRIPT_NAME'] ?>" method="POST">
  <table border="0">
    <tr>
      <td>Nome</td>
      <td>Endereço</td>
    </tr>
    <tr>
      <td><input type="text" name="NAME" maxlength="45" size="30" /></td>
      <td><input type="text" name="ADDRESS" maxlength="90" size="60" /></td>
      <td><input type="submit" value="Adicionar Funcionário" /></td>
    </tr>
  </table>
</form>

<!-- Lista de Funcionários -->
<h2>Lista de Funcionários</h2>
<table border="1" cellpadding="2" cellspacing="2">
  <tr>
    <th>ID</th>
    <th>Nome</th>
    <th>Endereço</th>
  </tr>
<?php
$result = pg_query($connection, "SELECT * FROM EMPLOYEES");
while($query_data = pg_fetch_row($result)) {
  echo "<tr>";
  echo "<td>",$query_data[0], "</td>",
       "<td>",$query_data[1], "</td>",
       "<td>",$query_data[2], "</td>";
  echo "</tr>";
}
?>
</table>

<hr>

<!-- Formulário de Receitas -->
<h2>Adicionar Receita Favorita do Colaborador</h2>
<form action="<?PHP echo $_SERVER['SCRIPT_NAME'] ?>" method="POST">
  <table border="0">
    <tr><td>Nome do Colaborador:</td><td><input type="text" name="FOOD_NAME" required /></td></tr>
    <tr><td>Comida Preferida:</td><td><input type="text" name="FAVORITE_FOOD" required /></td></tr>
    <tr><td>Tempo de Preparo (min):</td><td><input type="number" name="PREP_TIME" required /></td></tr>
    <tr><td>Data do Registro:</td><td><input type="date" name="CREATED_AT" required /></td></tr>
    <tr><td colspan="2"><input type="submit" value="Salvar Receita" /></td></tr>
  </table>
</form>

<!-- Lista de Receitas -->
<h2>Receitas Favoritas dos Colaboradores</h2>
<table border="1" cellpadding="2" cellspacing="2">
  <tr>
    <th>ID</th>
    <th>Nome do Colaborador</th>
    <th>Comida Preferida</th>
    <th>Tempo de Preparo</th>
    <th>Data de Registro</th>
  </tr>
<?php
$result_foods = pg_query($connection, "SELECT * FROM FAVORITE_FOODS ORDER BY id DESC");
while($row = pg_fetch_assoc($result_foods)) {
  echo "<tr>";
  echo "<td>{$row['id']}</td>";
  echo "<td>{$row['employee_name']}</td>";
  echo "<td>{$row['favorite_food']}</td>";
  echo "<td>{$row['prep_time']} min</td>";
  echo "<td>{$row['created_at']}</td>";
  echo "</tr>";
}
?>
</table>

<!-- Encerramento -->
<?php
  pg_free_result($result);
  pg_free_result($result_foods);
  pg_close($connection);
?>

</body>
</html>

<?php
/* Adiciona funcionário */
function AddEmployee($connection, $name, $address) {
   $n = pg_escape_string($name);
   $a = pg_escape_string($address);
   $query = "INSERT INTO EMPLOYEES (NAME, ADDRESS) VALUES ('$n', '$a');";
   pg_query($connection, $query);
}

/* Cria tabela de funcionários */
function VerifyEmployeesTable($connection, $dbName) {
  if(!TableExists("EMPLOYEES", $connection, $dbName)) {
     $query = "CREATE TABLE EMPLOYEES (
         ID serial PRIMARY KEY,
         NAME VARCHAR(45),
         ADDRESS VARCHAR(90)
       )";
     pg_query($connection, $query);
  }
}

/* Adiciona receita */
function AddFavoriteFood($connection, $employee_name, $favorite_food, $prep_time, $created_at) {
   $e = pg_escape_string($employee_name);
   $f = pg_escape_string($favorite_food);
   $p = intval($prep_time);
   $d = pg_escape_string($created_at);
   $query = "INSERT INTO FAVORITE_FOODS (employee_name, favorite_food, prep_time, created_at) VALUES ('$e', '$f', $p, '$d');";
   pg_query($connection, $query);
}

/* Cria tabela de receitas */
function VerifyFavoriteFoodsTable($connection, $dbName) {
  if(!TableExists("FAVORITE_FOODS", $connection, $dbName)) {
     $query = "CREATE TABLE FAVORITE_FOODS (
         id serial PRIMARY KEY,
         employee_name VARCHAR(100),
         favorite_food VARCHAR(100),
         prep_time INTEGER,
         created_at DATE
       )";
     pg_query($connection, $query);
  }
}

/* Verifica se tabela existe */
function TableExists($tableName, $connection, $dbName) {
  $t = strtolower(pg_escape_string($tableName));
  $query = "SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_NAME = '$t';";
  $checktable = pg_query($connection, $query);
  return (pg_num_rows($checktable) > 0);
}
?>
