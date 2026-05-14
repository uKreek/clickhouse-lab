<?php
require 'vendor/autoload.php';

// Добавляем стили
echo "
<style>
    body {
        background-color: #d986b1; /* Нежно-розовый фон */
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-height: 100vh;
        margin: 0;
        color: #4a4a4a;
    }

    h3, h4 {
        color: #8b1b49; /* Насыщенный розовый для заголовков */
        text-transform: uppercase;
        letter-spacing: 2px;
    }

    table {
        background: white;
        border-collapse: collapse;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        border-radius: 12px;
        overflow: hidden;
        border: none;
        margin-top: 20px;
    }

    th {
        background-color: #ffb6c1; /* Розовый для шапки таблицы */
        color: white;
        padding: 15px 25px;
    }

    td {
        padding: 12px 20px;
        text-align: center;
        border-bottom: 1px solid #eee;
    }

    tr:hover {
        background-color: #fff0f5; /* Подсветка строки при наведении */
    }
    
    p {
        font-weight: bold;
    }
</style>
";


require 'vendor/autoload.php';

use App\ClickhouseExample;

echo "<h3>Работа с ClickHouse: Транспорты</h3>";

$click = new ClickhouseExample();

// 1. Проверяем доступность ClickHouse (считаем системные таблицы)
echo "<p>Количество системных таблиц: " . $click->query('SELECT count() FROM system.tables') . "</p>";

// 2. Создаем таблицу transports
// Используем движок MergeTree, обязательный для большинства задач в ClickHouse
$createTableSql = "
    CREATE TABLE IF NOT EXISTS transports (
       id UInt32,
       type String,
       brand String,
       capacity UInt16,
       created_at DateTime DEFAULT now()
    ) ENGINE = MergeTree()
    ORDER BY id;
";
$click->query($createTableSql);
echo "<p>Таблица 'transports' успешно создана (или уже существует).</p>";

// 3. Очищаем таблицу перед вставкой (чтобы при перезагрузке страницы данные не дублировались)
$click->query("TRUNCATE TABLE transports;");

// 4. Вставляем данные о транспорте
$insertSql = "
    INSERT INTO transports (id, type, brand, capacity) VALUES 
    (1, 'Велосипед', 'Кама', 1), 
    (2, 'Поезд', 'РЖД', 350),
    (3, 'Сани', 'Дедушка Мороз', 2),
    (4, 'Самолет', 'Уральские авиалини', 186);
";
$click->query($insertSql);
echo "<p>Данные о транспорте добавлены.</p>";

// 5. Получаем и выводим данные
// ClickHouse по умолчанию отдает данные в формате TSV, но мы можем запросить JSON
$selectSql = "SELECT * FROM transports FORMAT JSON;";
$resultJson = $click->query($selectSql);

$data = json_decode($resultJson, true);

// Красивый вывод результата
echo "<h4>Список транспорта:</h4>";
echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
echo "<tr><th>ID</th><th>Тип</th><th>Марка</th><th>Вместимость (чел)</th><th>Дата записи</th></tr>";

if (isset($data['data'])) {
    foreach ($data['data'] as $row) {
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>{$row['type']}</td>";
        echo "<td>{$row['brand']}</td>";
        echo "<td>{$row['capacity']}</td>";
        echo "<td>{$row['created_at']}</td>";
        echo "</tr>";
    }
}
echo "</table>";