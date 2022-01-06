<?php

require_once('/var/www/html/librairie/Database.php');

/**
 * @return string|false
 */
function getUserInput(string $message){
    echo $message;
    $handle = fopen("php://stdin", "r");
    $line = fgets($handle);
    fclose($handle);

    return trim($line);
}

function createModelFile(string $model_name, array $element, $database_name){
    $var_ele = "private ";
    $construct = "public function __construct(";
    $construct_setter = "   \$this";
    $mutator = "";
    $format = "
        public static function format(\$data){\n
            \$objs = [];\n
            if (\$data != NULL){\n
                foreach(\$data as \$d){\n";
    $format_new = "";
    foreach($element as $e => $type){
        if ($var_ele != "private ")
            $var_ele .= ",";
        $var_ele .= "\$$e";

        if ($construct != "public function __construct(")
            $construct .= ",";
        $construct .= "\$$e = null";

        $a = $e;
        $a[0] = strtoupper($a[0]);
        if (strpos($a, '_') != false){
            $a[strpos($a, '_')+1] = strtoupper($a[strpos($a, '_')+1]);
            $a = str_replace('_', '', $a);
        }

        $construct_setter .= "->set$a(\$$e)";

        $mutator .= "
        public function set$a(\$$e){
            \$this->$e = \$$e;
            return \$this;
        }
        public function get$a(){
            return \$this->$e;
        }\n";

        $format .= "
                    \$$e = \Controller\ControllerController::keyExist('$e', \$d);";

        if ($format_new != "")
            $format_new .= ",";
        $format_new .= "\$$e";
    }
    $var_ele .= ";";
    $construct_setter .= ";";

    $construct .= "){
        $construct_setter
        }\n";
    $a = strtolower($model_name);
    $format .= "
                    \$$a = new self($format_new);\n
                    array_push(\$objs, \$$a);
                }
            }
            return (empty(\$objs)) ? null : \$objs;
        }";

    $file = fopen("../Model/$model_name.php", 'w');
    fwrite($file, "<?php
    namespace Model;
    Class $model_name{
        $var_ele\n
        $construct
        $mutator
        $format
    }");
    fclose($file);

    createControllerFile($model_name, $database_name);
}

function createControllerFile(string $model_name, $database_name){
    $file = fopen("../Controller/{$model_name}Controller.php", 'w');
    $a = strtolower($model_name);
    fwrite($file, "<?php
    namespace Controller;
    Class {$model_name}Controller extends ControllerController{
        protected static \$table_name = \"$model_name\";
        protected static \$model_class = \Model\\$model_name::class;
        protected static \$database = \"$database_name\";
    }");
    fclose($file);
}

function readDatabaseFile(){
    $file = fopen("/var/www/html/librairie/config/database.json", 'r');
    $res = fread($file, 4000);
    fclose($file);

    return json_decode(trim($res), true);
}

function createDatabase(string $model_name, array $var_ele, string $database_name){
    $databases = readDatabaseFile();
    $db = null;
    foreach($databases as $db){
        if ($db['name'] == $database_name)
            $database = new \Database($db['type'], $db['host'], $db['port'], $db['database'], $db['user'], $db['password']);
    }

    $tq = null;
    switch($database->getDatabaseType()){
        case \Database::TYPE_MYSQL: $tq = "`";break;
        case \Database::TYPE_PGSQL: $tq = "\"";break;
    }   

    if ($database->getDatabaseType() == "mysql")
        $SQL = "SHOW TABLES LIKE '$model_name'";
    else if ($database->getDatabaseType() == "pgsql")
        $SQL = "SELECT EXISTS (SELECT 1 FROM pg_tables WHERE schemaname = 'public' AND tablename = '$model_name');";
    $req = $database->getPDO()->prepare($SQL);
    if (!$req->execute())
        return $SQL;

    if ($database->getDatabaseType() == "mysql"){
        if (!empty($req->fetchAll())){
            $req = $database->getPDO()->prepare("DROP TABLE $tq$model_name$tq");
            if (!$req->execute())
                return $SQL;
        }
    }else if ($database->getDatabaseType() == "pgsql"){
        if ($req->fetch()['exists'] == true){
            $req = $database->getPDO()->prepare("DROP TABLE $tq$model_name$tq");
            if (!$req->execute())
                return $SQL;
        }
    }

    if ($database->getDatabaseType() == "mysql"){
        $SQL = "CREATE TABLE $model_name (";
        $primary_key = null;
        foreach($var_ele as $key => $value){
            if ($SQL !== "CREATE TABLE $model_name (")
                $SQL .= ", ";
            $value = strtoupper($value);

            if ($value == "SERIAL"){
                $SQL .= "$tq$key$tq INT NOT NULL AUTO_INCREMENT";
                $primary_key = $key;
            }
            else
                $SQL .= "$tq$key$tq $value NOT NULL";
        }
        if ($primary_key != "SERAIL")
            $SQL .= ", PRIMARY KEY(`$primary_key`)";
        $SQL .= ") ENGINE = InnoDB;";
    }else if ($database->getDatabaseType() == "pgsql"){
        $SQL = "CREATE TABLE public.\"$model_name\" (";
        foreach($var_ele as $key => $value){
            if ($SQL !== "CREATE TABLE public.\"$model_name\" (")
                $SQL .= ", ";

            if ($value == "int")
                $value = "integer";
            $SQL .= "\"$key\" $value";
        }
        $SQL .= ");";
    }

    $req = $database->getPDO()->prepare($SQL);
    if (!$req->execute())
        return $SQL;

    if ($database->getDatabaseType() == "pgsql"){
        $SQL = "ALTER TABLE public.\"$model_name\" OWNER to ".$database->getUser();

        $req = $database->getPDO()->prepare($SQL);
        if (!$req->execute())
            return $SQL;
    }

    return true;

}

$database_name = getUserInput("Entre le que vous avez donner à votre base de donnée: ");
$model_name = getUserInput("Entrez un nom pour le Model (la première lettre en majuscule): ");

echo "Création des tables (ne pas mettre d'espace mais des '_').\n";
$var_ele = [];
$var = "";
while($var != "-1"){
    if ($var != "")
        $var_ele[$var] = $type;
    $var = getUserInput("Nom de la colonne (-1 pour arrêter): ");
    if ($var == "-1")
        break;
    $type = getUserInput("Type de donnée (int, text, serial)");
}

if (createDatabase($model_name, $var_ele, $database_name))
    createModelFile($model_name, $var_ele, $database_name);

?>
