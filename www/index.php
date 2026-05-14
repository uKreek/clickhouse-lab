<?php

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
    (1, 'Bus', 'Mercedes-Benz', 45), 
    (2, 'Train', 'Siemens Desiro', 350),
    (3, 'Car', 'Toyota Camry', 5),
    (4, 'Airplane', 'Boeing 737', 189);
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