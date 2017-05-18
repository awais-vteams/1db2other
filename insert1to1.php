<?php
//error_reporting(-1);

if (isset($_POST['user'])) {
    $user = $_POST['user'];
    $pass = $_POST['pwd'];
    $db1 = $_POST['db1'];
    $db2 = $_POST['db2'];
    $tb1 = $_POST['tb1'];
    $tb2 = $_POST['tb2'];

    try {
        $db1h = new PDO("mysql:host=localhost;dbname=$db1", $user, $pass);
        $db2h = new PDO("mysql:host=localhost;dbname=$db2", $user, $pass);

        $q = $db1h->prepare("DESCRIBE $tb1");
        $q->execute();
        $table1_fields = $q->fetchAll();

        $q1 = $db2h->prepare("DESCRIBE $tb2");
        $q1->execute();
        $table2_fields = $q1->fetchAll();


    } catch (PDOException $e) {
        print "Error!: " . $e->getMessage() . "<br/>";
        //die();
    }

    if (isset($_POST['getsql'])) {
        $col1 = [];
        $col2 = [];

        foreach ($_POST['col'] as $col => $value) {
            if ($value == "" && $_POST["txt_$col"] == "") continue;

            $col1[] = $col;
            $col2[] = ($_POST["txt_$col"] != "") ? $_POST["txt_$col"] : $value;
        }

        $col1 = implode(', ', $col1);
        $col2 = implode(', ', $col2);
        $sql = '';
        $opt = isset($_POST['opt']) ? $_POST['opt'] : 'INSERT';

        if (isset($_POST['foreign_keys'])) {
            $sql .= "SET FOREIGN_KEY_CHECKS=0; \n";
        }

        $sql .= "{$opt} INTO {$db2}.{$tb2} ({$col1}) SELECT $col2 FROM {$db1}.{$tb1}; \n";

        if (isset($_POST['foreign_keys'])) {
            $sql .= "SET FOREIGN_KEY_CHECKS=1; ";
        }

        print "<div class='container'><textarea class='form-control' style='width: 100%; height: 120px; margin-top: 20px'>{$sql}</textarea></div>";
    }

    if (isset($_POST['getcount'])) {
        $q = $db1h->prepare("SELECT COUNT(*) FROM {$tb1}");
        $q->execute();
        $tb1c = "Rows: " . $q->fetchColumn();

        $q = $db2h->prepare("SELECT COUNT(*) FROM {$tb2}");
        $q->execute();
        $tb2c = "Rows: " . $q->fetchColumn();
    }
}


function getSelect($columns, $name, $value = '')
{
    $sel = "<select name='col[$name]'>";
    $sel .= "<option value=''>--</option>";
    foreach ($columns as $col) {
        $selected = ($col['Field'] == $value) ? "selected='selected'" : '';
        $sel .= "<option $selected value='$col[Field]'>$col[Field] : $col[Type]</option>";
    }
    $sel .= "</select>";

    return $sel;
}

?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Insert from one DB to Other</title>
    <link rel="stylesheet" type="text/css" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <style type="text/css">
        .table {
            font-size: 13px;
        }
        body {
            background: #f0f0f0;
        }
        .container {
            background: #fff;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Get Query Insert From one DB to Other</h2>
    <form action="" method="post">
        <div class="">
            <div class="pull-left">
                <input class="form-control" type="text" value="<?= $_POST['user'] ?>" placeholder="Database Username"
                       name="user" required>
                <input class="form-control" type="text" value="<?= $_POST['db1'] ?>" placeholder="First Database Name"
                       name="db1" required>
                <input class="form-control" type="text" value="<?= $_POST['db2'] ?>" placeholder="Second Database Name"
                       name="db2" required>
            </div>
            <div class="pull-left">
                <input class="form-control" type="text" value="<?= $_POST['pwd'] ?>" placeholder="Database Password"
                       name="pwd" required>
                <input class="form-control" type="text" value="<?= $_POST['tb1'] ?>" placeholder="First Table name"
                       name="tb1" required>
                <strong><?= $tb1c ?></strong>
                <input class="form-control" type="text" value="<?= $_POST['tb2'] ?>" placeholder="Second Table name"
                       name="tb2" required>
                <strong><?= $tb2c ?></strong>
            </div>
            <div class="clearfix"></div>
        </div>

        <p></p>
        <input type="submit" name="submit" value="Submit" class="btn btn-success">
        <input type="submit" name="getcount" value="Get Count" class="btn btn-primary">

        <hr>
        <?php if (isset($_POST['user'])) { ?>
            <select name="opt" id="opt" style="margin-right: 20px">
                <option <?= ($_POST['opt'] == 'INSERT') ? "selected='selected'" : ''; ?> value="INSERT">INSERT</option>
                <option <?= ($_POST['opt'] == 'REPLACE') ? "selected='selected'" : ''; ?> value="REPLACE">REPLACE
                </option>
            </select>
            <input type="checkbox" name="foreign_keys"
                   id="foreign_keys" <?= (isset($_POST['foreign_keys'])) ? 'checked="checked"' : '' ?> value="1">
            <label for="foreign_keys">Disable Foreign Keys </label>
            <br>

            <table style="margin-top: 20px;" class="table table-hover table-striped">
                <thead>
                <tr>
                    <th>#</th>
                    <th><?= $_POST['tb2'] ?></th>
                    <th><?= $_POST['tb1'] ?></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($table2_fields as $count => $col) { ?>
                    <tr>
                        <td><?= $count + 1 ?></td>
                        <td><?= $col['Field'] . ' : ' . $col['Type'] . '<br/>default: ' . $col['Default'] ?></td>
                        <td>
                            <?= getSelect($table1_fields, $col['Field'], $_POST["col"][$col['Field']]) ?>
                            <input placeholder="Other Value"
                                   value="<?= $_POST["txt_" . $col['Field']] ?>" type="text"
                                   name="<?= "txt_" . $col['Field'] ?>">
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
            <input type="submit" value="Submit" name="getsql" class="btn btn-success">
        <?php } ?>
    </form>
</div>
</body>
</html>
