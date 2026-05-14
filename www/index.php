<?php
require 'vendor/autoload.php';
use App\Helpers\ClickhouseExample;

// инициализация клиента
$click = new ClickhouseExample();

// создание таблицы
$click->query("
    CREATE TABLE IF NOT EXISTS sensors (
        id UUID DEFAULT generateUUIDv4(),
        sensor_name String,
        temperature Float32,
        humidity Float32,
        timestamp DateTime DEFAULT now()
    ) ENGINE = MergeTree()
    ORDER BY timestamp;
");

// обработка формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sensorName = htmlspecialchars($_POST['sensor_name']);
    $temperature = floatval($_POST['temperature']);
    $humidity = floatval($_POST['humidity']);
    
    // вставка данных
    $click->query(sprintf(
        "INSERT INTO sensors (sensor_name, temperature, humidity) VALUES ('%s', %f, %f)",
        $sensorName, $temperature, $humidity
    ));
    
    // перенаправление 
    header("Location: /");
    exit();
}

// получение всех данных
$response = $click->query("SELECT * FROM sensors ORDER BY timestamp DESC FORMAT JSON");
$data = json_decode($response, true);
$sensors = $data['data'] ?? [];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>отслеживание сенсоров</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: #f9fafb;
            color: #374151;
            margin: 0;
            padding: 2rem;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .container {
            max-width: 800px;
            width: 100%;
        }
        h1, h2 {
            font-weight: 500;
        }
        .card {
            background: #fff;
            border-radius: 8px;
            padding: 2rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            margin-bottom: 2rem;
        }
        form {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        label {
            font-size: 0.875rem;
            font-weight: 500;
        }
        input {
            padding: 0.5rem;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            font-size: 1rem;
            outline: none;
            transition: border-color 0.2s;
        }
        input:focus {
            border-color: #3b82f6;
        }
        button {
            background-color: #2563eb;
            color: white;
            padding: 0.75rem;
            border: none;
            border-radius: 4px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        button:hover {
            background-color: #1d4ed8;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            text-align: left;
            padding: 1rem;
            border-bottom: 1px solid #e5e7eb;
        }
        th {
            background-color: #f3f4f6;
            font-weight: 500;
        }
        tr:last-child td {
            border-bottom: none;
        }
        .empty {
            text-align: center;
            color: #6b7280;
            padding: 2rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <h1>добавить данные сенсора</h1>
            <form method="POST" action="/">
                <div>
                    <label for="sensor_name">имя сенсора</label><br>
                    <input type="text" id="sensor_name" name="sensor_name" required style="width: 100%;">
                </div>
                <div>
                    <label for="temperature">температура (°C)</label><br>
                    <input type="number" id="temperature" name="temperature" step="0.1" required style="width: 100%;">
                </div>
                <div>
                    <label for="humidity">влажность (%)</label><br>
                    <input type="number" id="humidity" name="humidity" step="0.1" required style="width: 100%;">
                </div>
                <button type="submit">сохранить</button>
            </form>
        </div>

        <div class="card">
            <h2>история показаний</h2>
            <?php if (count($sensors) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>сенсор</th>
                            <th>температура</th>
                            <th>влажность</th>
                            <th>время</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sensors as $sensor): ?>
                            <tr>
                                <td><?= htmlspecialchars($sensor['sensor_name']) ?></td>
                                <td><?= number_format($sensor['temperature'], 1) ?> °C</td>
                                <td><?= number_format($sensor['humidity'], 1) ?> %</td>
                                <td><?= htmlspecialchars($sensor['timestamp']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty">нет данных для отображения</div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>