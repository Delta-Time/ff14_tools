<?php
$ready_time_begin = microtime(true);

$link = new PDO("sqlite:d.sqlite");

$job_accept = ["戦", "園", "掘"];
$level_accept = [];
for ($i = 0; $i <= 80; $i++) {
    $level_accept[] = $i;
}

$sql = "SELECT name FROM item_list;";
$resource = $link->query($sql);
if (!$resource) {
    die();
}

$row = $resource->fetchAll(PDO::FETCH_NUM);
foreach ($row as $r) {
    $item_accept[] = $r[0];
}
sort($item_accept);

if (isset($_POST["item_name"]) && $_POST["item_name"] != "") {

    $search_value = $_POST["item_name"];
    if (mb_substr($search_value, -1) === "" || mb_substr($search_value, -1) === "") { // HQ記号, 収集品記号
        $search_value = mb_substr($search_value, 0, -1); //それらは除外して検索可能にする
    }
    $sql = "SELECT * FROM item_list WHERE name LIKE ?";
    $statement = $link->prepare($sql);
    $statement->bindValue(1, $search_value . "%");
    $statement->execute();

    $search_result = $statement->fetchAll(PDO::FETCH_ASSOC);
}

$link = null;
$ready_time_end = microtime(true);
$ready_time = ($ready_time_end - $ready_time_begin);

?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet"
          href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/themes/ui-lightness/jquery-ui.css"/>
    <script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1/jquery-ui.min.js"></script>
    <script type="text/javascript">
        var itemlist = [
            <?php
            echo htmlspecialchars(join($item_accept, "," . PHP_EOL));
            ?>
        ];

        jQuery(function () {
            jQuery('#item_list').autocomplete({
                source: itemlist,
                autoFocus: true,
                delay: 500,
                minLength: 3
            });
        });
    </script>
    <title>リテイナーベンチャーアイテム検索</title>
</head>

<body>
<h2>FF14 リテイナーベンチャーアイテム検索</h2>
<form method="post" action="index.php">
    <label>アイテム名（前方一致）</label>
    <input type=text name="item_name" id="item_list" value="<?php if (isset($search_value)) {
        echo htmlspecialchars($search_value);
    } ?>">
    <button type="submit">検索</button>
</form>
<div id="result">
    <table border="1">
        <tr>
            <th>職</th>
            <th>Lv</th>
            <th>アイテム</th>
        </tr>
        <?php
        if (isset($search_result) && count($search_result) === 0) {
            echo '<tr><td colspan="3" style="color: gray">検索結果はありませんでした</td></tr>';
        } else if (isset($search_result) && count($search_result) >= 1) {
            foreach ($search_result as $result) {
                $job = htmlspecialchars($result["job"]);
                $level = htmlspecialchars($result["level"]);
                $name = htmlspecialchars($result["name"]);
                echo "<tr><td>{$job}</td><td align='right'>{$level}</td><td>{$name}</td></tr>";
            }
        } else {
            echo '<tr><td colspan="3" style="color: darkslategrey">アイテム名を入力して「検索」してください。</td></tr>';
        }
        ?>
    </table>
    <p style="font-size: small">職凡例: [戦]戦闘系ジョブ [掘]採掘師 [園]園芸師 </p>
    <p style="font-size: small">アイテム名にはHQや収集品の記号をそのまま入力可能です。</p>
</div>
<div id="footer" style="line-height: 0.5">
    <hr size="1">
    <pre>記載されている会社名・製品名・システム名などは、各社の商標、または登録商標です。</pre>
    <pre>Copyright (C) 2010 - 2020 SQUARE ENIX CO., LTD. All Rights Reserved. </pre>
    Git: <a href="https://github.com/Delta-Time/ff14_tools/"><pre>https://github.com/Delta-Time/ff14_tools/</pre></a>
    <pre style="color: gray">処理時間: <?php echo htmlspecialchars(number_format($ready_time, 3)); ?>sec.</pre>
</div>
</body>
</html>
