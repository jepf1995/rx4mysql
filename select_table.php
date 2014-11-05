<?php
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


$stmt = $dbh->prepare("show tables");
$stmt->execute();


$indexName = "Tables_in_{$dbname}";

require_once 'html_ini.php';
?>
<link rel="stylesheet" href="pm-common-new.css" type="text/css" />

<script>
    // JavaScript Document
    $(document).ready(function () {
        $('#search').keyup(function(event) {
            var search_text = $('#search').val();
            var rg = new RegExp(search_text,'i');
            $('#product_list .product-list .product').each(function(){
                if($.trim($(this).html()).search(rg) == -1) {
                    $(this).parent().css('display', 'none');
                    $(this).css('display', 'none');
                    $(this).next().css('display', 'none');
                    $(this).next().next().css('display', 'none');
                }	
                else {
                    $(this).parent().css('display', '');
                    $(this).css('display', '');
                    $(this).next().css('display', '');
                    $(this).next().next().css('display', '');
                }
            });
        });
    });

</script>


<h2>Select a table to generate a class</h2>

<div style="text-align: center">
Or type to search:<br/>
<input name="" type="text"  id="search" autocomplete="off" style="width: 500px" />

<br/>
<div id="product_list" style="width: 100%;height: 320px;overflow: auto">

    <div class="product-list">
        <ul>
            <?php while ($row = $stmt->fetch()) { ?>
                <li class="litem">
                    <div class="product"><a href="done.php?table=<?php echo($row["$indexName"]); ?>"> <?php echo($row["$indexName"]); ?></a></div>
                </li>
            <?php } ?>
        </ul>
    </div>
</div>

</div>


<script src="jquery.js" type="text/javascript"></script>
<script src="pm_new.js" type="text/javascript"></script>


<?php
unset($dbh);
unset($stmt);

require_once 'html_fim.php';
?>