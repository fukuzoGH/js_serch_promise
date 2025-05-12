<?php

// 検索文字列取得
$search = isset($_POST['searchtxt']) ? trim($_POST['searchtxt']) : '';

// DB接続
$db = new PDO('sqlite:test.db');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// SQL
if ($search !== '') {
    $stmt = $db->prepare('SELECT id,name FROM users WHERE name LIKE :name');
    $stmt->bindValue(':name', '%' . $search . '%', PDO::PARAM_STR);
} else {
    $stmt = $db->prepare('SELECT id,name FROM users');
}

//SQL実行
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>検索ページ</title>
</head>
<body>

    <form id="searchForm" action="." method="post">
        <label for="searchtxt">文字列:</label>
        <input name="searchtxt" id="searchtxt" value="">
        <button id="searchBtn" type="button">検索</button>
    </form>

	<div id="loading" style="display:none;">
		処理中...
	</div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>名前</th>
            </tr>
        </thead>
        <tbody id="tableBody">
            <?php foreach ($results as $item) { ?>
            <tr>
                <td><?php echo "{$item['id']}" ?></td>
                <td><?php echo "{$item['name']}" ?></td>
            </tr>
            <?php } ?>
        </tbody>
    </table>


<script>
document.getElementById('searchBtn').addEventListener('click', async () => {
    document.getElementById('loading').style.display = 'block';

    const formData = new FormData(document.getElementById('searchForm'));

    fetchData('.', formData)
        .then(updateTable)
        .catch(handleError)
        .finally(() => document.getElementById('loading').style.display = 'none');
});

function fetchData(url, formData) {
    return new Promise((resolve, reject) => {
        fetch(url, {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTPエラー ${response.status}`);
            }
            return response.text();
        })
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            resolve(doc.getElementById('tableBody').innerHTML);
        })
        .catch(error => reject(error));
    });
}

function updateTable(newTableBody) {
    document.getElementById('tableBody').innerHTML = newTableBody;
}

function handleError(error) {
    console.error('検索処理中にエラーが発生しました:', error);
    alert('検索処理に失敗しました。時間をおいて再試行してください。');
}
</script>

</body>
</html>

