<?php
error_reporting(-1);

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
        $table1_fields = $q->fetchAll(PDO::FETCH_COLUMN);

        $q1 = $db2h->prepare("DESCRIBE $tb2");
        $q1->execute();
        $table2_fields = $q1->fetchAll(PDO::FETCH_COLUMN);


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

        $sql = "INSERT INTO {$db2}.{$tb2} ({$col1}) SELECT $col2 FROM {$db1}.{$tb1};";

        print "<textarea style='width: 100%; height: 120px'>{$sql}</textarea>";
    }
}


function getSelect($columns, $name, $value = '')
{
    $sel = "<select name='col[$name]'>";
    $sel .= "<option value=''>--</option>";
    foreach ($columns as $col) {
        $selected = ($col == $value) ? "selected='selected'" : '';
        $sel .= "<option $selected value='$col'>$col</option>";
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
    <style>
        body {
            font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
            font-size: 12px;
        }

        select {
            padding: 5px;
        }

        input {
            padding: 5px;
            margin: 5px;
        }

        table td {
            padding: 2px 5px 2px 5px;
        }
    </style>
</head>
<body>
<div>
    <h2>Get Query Insert From one DB to Other</h2>
    <form action="" method="post">
        <input type="text" value="<?= $_POST['user'] ?>" placeholder="Database Username" name="user" required>
        <input type="text" value="<?= $_POST['pwd'] ?>" placeholder="Database Password" name="pwd" required> <br>
        <input type="text" value="<?= $_POST['db1'] ?>" placeholder="First Database Name" name="db1" required>
        <input type="text" value="<?= $_POST['tb1'] ?>" placeholder="First Table name" name="tb1" required> <br>
        <input type="text" value="<?= $_POST['db2'] ?>" placeholder="Second Database Name" name="db2" required>
        <input type="text" value="<?= $_POST['tb2'] ?>" placeholder="Second Table name" name="tb2" required> <br>
        <input type="submit" value="Submit">

        <hr>
        <?php if (isset($_POST['user'])) { ?>
            <table>
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
                        <td><?= $col ?></td>
                        <td>
                            <?= getSelect($table1_fields, $col, $_POST["col"][$col]) ?>
                            <input value="<?= $_POST["txt_" . $col] ?>" type="text" name="<?= "txt_" . $col ?>">
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
            <input type="submit" value="Submit" name="getsql">
        <?php } ?>
    </form>
</div>
</body>
</html>
