<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление координатами</title>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .container {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        .section {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
        }
        h2 {
            margin-top: 0;
        }
        input, button, select {
            padding: 8px 12px;
            border-radius: 4px;
            border: 1px solid #ccc;
            font-family: inherit;
        }
        button {
            background-color: #1E90FF;
            color: white;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        #message {
            padding: 10px;
            border-radius: 4px;
            margin-top: 10px;
            display: none;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Управление координатами</h1>

    <div class="section">
        <h2>Текущие координаты</h2>
        <table id="coordinatesTable">
            <thead>
            <tr>
                <th>№</th>
                <th>Широта</th>
                <th>Долгота</th>
                <th>Действие</th>
            </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

    <div class="section">
        <h2>Добавить новые координаты</h2>
        <div>
            <input type="text" id="newCoords" placeholder="Введите координаты (например, 55.123456,37.123456)">
            <button id="addButton">Добавить</button>
        </div>
        <div id="message"></div>
    </div>
</div>

<script>
    // Загрузка текущих координат
    function loadCoordinates() {
        fetch('get_addresses.php')
            .then(response => response.json())
            .then(data => {
                const tbody = document.querySelector('#coordinatesTable tbody');
                tbody.innerHTML = '';

                data.forEach((coords, index) => {
                    const [lat, lng] = coords.split(',');
                    const row = document.createElement('tr');

                    row.innerHTML = `
                            <td>${index + 1}</td>
                            <td>${lat}</td>
                            <td>${lng}</td>
                            <td><button class="removeBtn" data-coords="${coords}">Удалить</button></td>
                        `;

                    tbody.appendChild(row);
                });

                // Добавляем обработчики для кнопок удаления
                document.querySelectorAll('.removeBtn').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const coords = this.getAttribute('data-coords');
                        removeCoordinates(coords);
                    });
                });
            });
    }

    // Добавление координат
    document.getElementById('addButton').addEventListener('click', function() {
        const coords = document.getElementById('newCoords').value.trim();

        if (!coords) {
            showMessage('Введите координаты', 'error');
            return;
        }

        // Проверка формата координат
        if (!/^-?\d+\.?\d*,-?\d+\.?\d*$/.test(coords)) {
            showMessage('Некорректный формат координат. Используйте формат: широта,долгота', 'error');
            return;
        }

        fetch('update_addresses.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=add&coords=${encodeURIComponent(coords)}`
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage(data.message, 'success');
                    document.getElementById('newCoords').value = '';
                    loadCoordinates();
                } else {
                    showMessage(data.message, 'error');
                }
            })
            .catch(error => {
                showMessage('Ошибка при добавлении координат', 'error');
                console.error('Error:', error);
            });
    });

    // Удаление координат
    function removeCoordinates(coords) {
        if (!confirm('Вы уверены, что хотите удалить эти координаты?')) {
            return;
        }

        fetch('update_addresses.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=remove&coords=${encodeURIComponent(coords)}`
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage(data.message, 'success');
                    loadCoordinates();
                } else {
                    showMessage(data.message, 'error');
                }
            })
            .catch(error => {
                showMessage('Ошибка при удалении координат', 'error');
                console.error('Error:', error);
            });
    }

    // Показать сообщение
    function showMessage(text, type) {
        const messageBox = document.getElementById('message');
        messageBox.textContent = text;
        messageBox.className = type;
        messageBox.style.display = 'block';

        setTimeout(() => {
            messageBox.style.display = 'none';
        }, 3000);
    }

    // Загружаем координаты при загрузке страницы
    document.addEventListener('DOMContentLoaded', loadCoordinates);
</script>
</body>
</html>