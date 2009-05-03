<?php

// will need include of config.php
include_once('config.php');
include_once('adodb/adodb.inc.php');

function getdb()
{
    global $cfg;

    // 2004-12-09 PFM: connect to database.
    $db = NewADOConnection($cfg["db_type"]);
    $db->Connect($cfg["db_host"], $cfg["db_user"], $cfg["db_pass"], $cfg["db_name"]);
    if(!$db)
    {
        die ('Could not connect to database: '.$db->ErrorMsg().'<br>Check your database settings in the config.php file.');
    }
    return $db;
}

function showError($db, $sql)
{
    global $cfg;
    if($db->ErrorNo() != 0)
    {
        include("themes/matrix/index.php");
?>
<!DOCTYPE html
PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
        <title><?php echo $cfg["pagetitle"] ?></title>
        <link rel="StyleSheet" href="themes/matrix/style.css" type="text/css" />
    <meta http-equiv="pragma" content="no-cache" />
    <meta content="charset=iso-8859-1" />

</head>
<body bgcolor="<?php echo $cfg["main_bgcolor"] ?>">
<br /><br /><br />
<div align="center">
    <table border="1" bordercolor="<?php echo $cfg["table_border_dk"] ?>" cellpadding="0" cellspacing="0">
    <tr>
        <td>
        <table border="0" cellpadding="4" cellspacing="0" width="100%">
            <tr>
                    <td align="left" background="themes/matrix/images/bar.gif" bgcolor="<?php echo $cfg["main_bgcolor"] ?>">
                    <font class="title"><?php echo $cfg["pagetitle"] ?> Database/SQL Error</font>
                    </td>
            </tr>
        </table>
        </td>
    </tr>
    <tr>
        <td bgcolor="<?php echo $cfg["table_header_bg"] ?>">
        <div align="center">
        <table width="100%" bgcolor="<?php echo $cfg["body_data_bg"] ?>">
         <tr>
             <td>
             <table bgcolor="<?php echo $cfg["body_data_bg"] ?>" width="740 pixels" cellpadding="1">
             <tr>
                 <td>
                    <div align="center">
                     <table border="0" cellpadding="4" cellspacing="0" width="90%">
                     <tr>
                     <td>
<?php
                    if ($cfg["debug_sql"])
                    {
                        echo "Debug SQL is on. <br><br>SQL: <strong>".$sql."</strong><br><br><br>";
                    }
                    echo "Database error: <strong>".$db->ErrorMsg()."</strong><br><br>";
                    echo "Always check your database variables in the config.php file.<br><br>"
?>
                    </td>
                    </tr>
                    </table>
                    </div>
                </td>
            </tr>
            </table>
            </td>
        </tr>
        </table>
        </div>
        </td>
    </tr>
    </table>

</div>

<?php
        exit();
    }
}
?>