<?php

function fieldlize($string) {
    $string = ucfirst(strtolower($string));
    $string = str_replace("_", "", $string);
    return $string;
}



$tabela = $_GET['table'];

session_start();

try {
    $hostname = $_SESSION['dados']['host'];            //host
    $dbname = $_SESSION['dados']['database'];            //db name
    $username = $_SESSION['dados']['user'];            // username like 'sa'
    $pw = $_SESSION['dados']['password'];                // password for the user

    $dbh = new PDO("mysql:host=$hostname;dbname=$dbname", "$username", "$pw");
} catch (PDOException $e) {
    header("Location: index.php");
}

// 


// search by the table fields
$sql = sprintf("SHOW COLUMNS FROM %s", $tabela);

$res = $dbh->prepare($sql);
$res2 = $dbh->prepare($sql);

$res->execute();
$res2->execute();

// find by the table primary keys
while($r = $res->fetch()){
   if($r['Key'] == 'PRI') {
       $pkList[] = $r['Field'];
   }
}


require_once 'html_ini.php';


$minusculo = strtolower($tabela);
$minusculo = ucfirst($minusculo);

$fields = $res2->fetchAll();



$cab = "";

?>
<p style="text-align: right">
    <a href="select_table.php" style="text-decoration: none;color: green">Pick another table</a>
</p>









<b>m<?php echo($minusculo); ?>.php </b><br/>
<textarea style="width: 99%;height: 500px">
<?php
    echo ("<?php\n");
    echo ($cab);
    
    echo("\n");
    echo "require_once 'model/db/dbConnection.php'; \n\n";
    //echo('require_once("model/db/dbConnection.php");'."\n\n");
    
    echo("class m$minusculo extends dbConnection {\n");

    //$arquivo="<?php\n"."\n".'require_once("model/db/dbConnection.php");'."\n\n"."class m$minusculo extends dbConnection {\n";
    $arquivo="<?php\n"."\n"."require_once 'model/db/dbConnection.php';"."\n\n"."class m$minusculo extends dbConnection {\n";

    foreach ($fields as $field) {
        echo "\t" . 'private $' . "{$field['Field']};\n";
        $arquivo .= "\t" . 'private $' . "{$field['Field']};\n";
    }

    echo("\n");
        $arquivo .= "\n";
    foreach ($fields as $field) {
        $min = fieldlize($field['Field']);
        echo "\t" . 'public function get' . $min . "(){\n";
        echo("\t\treturn " . '$this->' . $field['Field'] . ";\n");
        echo("\t}\n\n");
        $arquivo .= "\t" . 'public function get' . $min . "(){\n"."\t\treturn " . '$this->' . $field['Field'] . ";\n"."\t}\n\n";
    }


    echo("\n");
        $arquivo .= "\n";
    foreach ($fields as $field) {
        $min = fieldlize($field['Field']);
        echo "\t" . 'public function set' . $min . "(" . '$' . $min . "){\n";
        echo("\t\t" . '$this->' . $field['Field'] . '=$' . $min . ";\n");
        echo("\t}\n\n");
        $arquivo .= "\t" . 'public function set' . $min . "(" . '$' . $min . "){\n"."\t\t" . '$this->' . $field['Field'] . '=$' . $min . ";\n"."\t}\n\n";

    }
    echo("}\n");


    echo("?>\n");
    $arquivo .= "}\n"."?>\n";
    ?>
</textarea>
<?php
    $f = fopen("arquivos_gerados/entities/m$minusculo.php", "w+");
    fwrite($f, $arquivo);
?>










<br/> <br/>
<b>a<?php echo($minusculo); ?>.php</b><br/>
<textarea style="width: 99%;height: 500px">
<?php
    $arquivo = "";
    echo("<?php\n");
    echo ($cab);    
    echo("\n");
    //echo('require_once("model/entities/m'.$minusculo.'.php");'."\n\n");
    echo "require_once 'model/entities/m".$minusculo.".php';"."\n\n";

    echo("class a$minusculo extends m$minusculo {\n");
    
    $arquivo = "<?php\n"."\n"."require_once 'model/entities/m".$minusculo.".php';"."\n\n"."class a$minusculo extends m$minusculo {\n";

    $lstFields = "";
    $lstVars = "";
    
    $lstFieldsIns = "";
    $lstVarsIns = "";
    
    foreach ($fields as $field) {
        $lstFields.=", ".$field['Field'];
        $lstVars .= ", '%s'";
        
        if($field['Extra'] != 'auto_increment'){ // only create insert in fields that are not AI
            $lstFieldsIns.=", ".$field['Field'];
            $lstVarsIns .= ", '%s'";          
        }
    }
    echo("\n");
    echo("\t".'protected $sqlInsert="insert into '.$tabela.' ('.substr($lstFieldsIns,2).') values ('.substr($lstVarsIns,2).')";'."\n\n");
    
    $arquivo .= "\n"."\t".'protected $sqlInsert="insert into '.$tabela.' ('.substr($lstFieldsIns,2).') values ('.substr($lstVarsIns,2).')";'."\n\n";
    
    // now in the update they need to update all felds except pk fields
    $lstFields = "";
    foreach ($fields as $field) {
        if(!in_array($field['Field'], $pkList)){
            $lstFields.=", ".$field['Field']."='%s'";
        }
    }
    
    
    $i=0;
    // foreach pk's to mount where condition
    foreach ($pkList as $pk) {
        if($i==0){
            $strWhere = "$pk ='%s' ";
        }else{
            $strWhere.= ' and '.$pk."= '%s'";
        }
        $i++;
    }
    
    
    // if have some fild to update, otherwise do not create update method
    $update = false;
    if($lstFields != ''){
        echo("\t".'protected $sqlUpdate="update '.$tabela.' set '.substr($lstFields,2).' where '.$strWhere.'";'."\n\n");
        $arquivo .= "\t".'protected $sqlUpdate="update '.$tabela.' set '.substr($lstFields,2).' where '.$strWhere.'";'."\n\n";
        $update = true;
    }
    
    
    echo("\t".'protected $sqlSelect="select * from '.$tabela.' where %s %s";'."\n\n");
    $arquivo .= "\t".'protected $sqlSelect="select * from '.$tabela.' where %s %s";'."\n\n";
    
    echo("\t".'protected $sqlDelete="delete from '.$tabela.' where '.$strWhere.'";'."\n\n");
    $arquivo .= "\t".'protected $sqlDelete="delete from '.$tabela.' where '.$strWhere.'";'."\n\n\n";
    echo("\n");
    
    
    // insert
    echo("\tpublic function Insert(){\n");
    echo("\t\ttry {\n");
    $arquivo .= "\tpublic function Insert(){\n"."\t\ttry {\n";
    $strIns = '';
    foreach($fields as $field){
        
        if($field['Extra']!= 'auto_increment'){
            $strIns.= ',$this->get'.  fieldlize($field['Field']).'()';
        }
    }
    
    echo("\t\t\t".'$sql = sprintf($this->sqlInsert'.$strIns.');'."\n");
    $arquivo .= "\t\t\t".'$sql = sprintf($this->sqlInsert'.$strIns.');'."\n";
    echo("\t\t\t".'return $this->RunSelect($sql);'."\n");
    $arquivo .= "\t\t\t".'return $this->RunSelect($sql);'."\n";
    echo("\t\t".'} catch (Exception $e) {'."\n");
    $arquivo .= "\t\t".'} catch (Exception $e) {'."\n";
    echo("\t\t\t".'echo "Caught exception:",$e->getMessage(), "\n";'."\n");
    $arquivo .= "\t\t\t".'echo "Caught exception:",$e->getMessage(), "\n";'."\n";
    echo("\t\t}\n");  
    $arquivo .=  "\t\t}\n";    
    echo("\t}\n\n");
    $arquivo .= "\t}\n\n";
    
    
    // update
    if($update){
        echo("\tpublic function Update(){\n");
        $arquivo .= "\tpublic function Update(){\n";
        echo("\t\ttry {\n");
        $arquivo .= "\t\ttry {\n";
        
        $strUpdate = "";
        foreach ($fields as $field) {
            if(!in_array($field['Field'], $pkList)){
                $strUpdate.=', $this->get'.  fieldlize($field['Field']).'()';
            }
        }
        
        foreach($pkList as $pk){
            $strUpdate .= ',$this->get'.  fieldlize($pk.'()');
        }      

        echo("\t\t\t".'$sql = sprintf($this->sqlUpdate'.$strUpdate.');'."\n");
        $arquivo .= "\t\t\t".'$sql = sprintf($this->sqlUpdate'.$strUpdate.');'."\n";
        echo("\t\t\t".'return $this->RunSelect($sql);'."\n");
        $arquivo .= "\t\t\t".'return $this->RunSelect($sql);'."\n";
        echo("\t\t".'} catch (Exception $e) {'."\n");
        $arquivo .= "\t\t".'} catch (Exception $e) {'."\n";
        echo("\t\t\t".'echo "Caught exception:",$e->getMessage(), "\n";'."\n");
        $arquivo .= "\t\t\t".'echo "Caught exception:",$e->getMessage(), "\n";'."\n";
        echo("\t\t}\n");
        $arquivo .= "\t\t}\n";            
        echo("\t}\n\n");
        $arquivo .= "\t}\n\n";
    }

    
    
    // select - rquery recebe true se deve voltar a SQL, nao resultado
    echo("\tpublic function Select(".'$where="",$order="",$rquery=false'."){\n");
    $arquivo .= "\tpublic function Select(".'$where="",$order="",$rquery=false'."){\n";
    echo("\t\ttry {\n");
    $arquivo .= "\t\ttry {\n";
    echo("\t\t\t".'$sql = sprintf($this->sqlSelect, $where, $order);'."\n");
    $arquivo .= "\t\t\t".'$sql = sprintf($this->sqlSelect, $where, $order);'."\n";
    echo("\t\t\t".'if ($rquery)'."\n");
    $arquivo .= "\t\t\t".'if ($rquery)'."\n";
    echo("\t\t\t\t".'return $sql;'."\n");
    $arquivo .= "\t\t\t\t".'return $sql;'."\n";
    echo("\t\t\t".'else'."\n");
    $arquivo .= "\t\t\t".'else'."\n";
    echo("\t\t\t\t".'return $this->RunSelect($sql);'."\n");
    $arquivo .= "\t\t\t\t".'return $this->RunSelect($sql);'."\n";
    echo("\t\t".'} catch (Exception $e) {'."\n");
    $arquivo .= "\t\t".'} catch (Exception $e) {'."\n";
    echo("\t\t\t".'echo "Caught exception:",$e->getMessage(), "\n";'."\n");
    $arquivo .= "\t\t\t".'echo "Caught exception:",$e->getMessage(), "\n";'."\n";
    echo("\t\t}\n");
    $arquivo .= "\t\t}\n";
    echo("\t}\n\n");
    $arquivo .= "\t}\n\n";
    
    
    // delete
    echo("\tpublic function Delete(){\n");
    $arquivo .="\tpublic function Delete(){\n";
    echo("\t\ttry {\n");  
    $arquivo .=  "\t\ttry {\n";
    $strDel = '';
    foreach($pkList as $pk){
        $strDel .= ',$this->get'.  fieldlize($pk.'()');
    }
    
    echo("\t\t\t".'$sql = sprintf($this->sqlDelete'.$strDel.');'."\n");
    $arquivo .= "\t\t\t".'$sql = sprintf($this->sqlDelete'.$strDel.');'."\n";
    echo("\t\t\treturn ".'$this->RunQuery($sql);'."\n");
    $arquivo .= "\t\t\treturn ".'$this->RunQuery($sql);'."\n";
    echo("\t\t".'} catch (Exception $e) {'."\n");
    $arquivo .= "\t\t".'} catch (Exception $e) {'."\n";
    echo("\t\t\t".'echo "Caught exception:",$e->getMessage(), "\n";'."\n");
    $arquivo .= "\t\t\t".'echo "Caught exception:",$e->getMessage(), "\n";'."\n";
    echo("\t\t}\n");   
    $arquivo .= "\t\t}\n";
    echo("\t}\n\n");
    $arquivo .= "\t}\n\n";


    
    echo("\tpublic function load() {\n");
    $arquivo .= "\tpublic function load() {\n";
    echo("\t\ttry {\n");  
    $arquivo .=   "\t\ttry {\n";
    echo("\t\t\t".'$rs = $this->Select(sprintf("and '.$strWhere.'"'.$strDel.'));'."\n");
    $arquivo .= "\t\t\t".'$rs = $this->Select(sprintf("and '.$strWhere.'"'.$strDel.'));'."\n";
    
    foreach ($fields as $field) 
        echo("\t\t\t".'$this->set'.fieldlize($field['Field']).'($rs[0]["'.$field['Field'].'"]);'."\n");
    $arquivo .= "\t\t\t".'$this->set'.fieldlize($field['Field']).'($rs[0]["'.$field['Field'].'"]);'."\n";
    
    echo("\n\t\t\t".'return $this;'."\n\n");
    $arquivo .= "\n\t\t\t".'return $this;'."\n\n";
    
    echo("\t\t".'} catch (Exception $e) {'."\n");
    $arquivo .= "\t\t".'} catch (Exception $e) {'."\n";
    echo("\t\t\t".'echo "Caught exception:",$e->getMessage(), "\n";'."\n");
    $arquivo .= "\t\t\t".'echo "Caught exception:",$e->getMessage(), "\n";'."\n";
    echo("\t\t}\n");
    $arquivo .=   "\t\t}\n"; 
    echo("\t}\n\n");
    $arquivo .= "\t}\n\n";

    
    echo("}\n\n");    
    echo("?>\n");
    $arquivo .= "}\n\n"."?>\n";
    ?>
</textarea>        
<!-- GERAR ARQUIVO -->    
<?php
    $f = fopen("arquivos_gerados/actions/a$minusculo.php", "w+");
    fwrite($f, $arquivo);
?>


<br/> <br/>
<b><?php echo($minusculo); ?>.php</b><br/>
<textarea style="width: 99%;height: 100px">
<?php
    echo("<?php\n");
    $arquivo = "<?php\n";
    echo ($cab);    
    echo("\n");
    $arquivo .= "\n";
    echo "require_once 'model/actions/a".$minusculo.".php';"."\n\n";
    $arquivo .= "require_once 'model/actions/a".$minusculo.".php';"."\n\n";

    echo("class $minusculo extends a$minusculo{\n");
    $arquivo .= "class $minusculo extends a$minusculo{\n";
    echo("\n");
    $arquivo .= "\n";
    echo("}\n");
    $arquivo .= "}\n";

    echo("?>\n");
    $arquivo .= "?>\n";
    ?>
</textarea>            

<?php
    $f = fopen("arquivos_gerados/core/$minusculo.php", "w+");
    fwrite($f, $arquivo);
?>

<?php
require_once 'html_fim.php';
?>