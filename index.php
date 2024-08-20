<?php
require_once 'connect.php';

$type = $_GET['type'] ?? 'deals';
$action = $_GET['action'] ?? 'list';
$id = isset($_GET['id']) ? intval($_GET['id']) : null;

if ($type === 'contacts') {
    $sql = "SELECT * FROM contacts";
} else {
    $sql = "SELECT * FROM deals";
}
$result = mysqli_query($connect, $sql);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($type === 'contacts') {
        $first_name = mysqli_real_escape_string($connect, $_POST['first_name']);
        $last_name = mysqli_real_escape_string($connect, $_POST['last_name']);
        
        if ($action === 'create') {
            $sql = "INSERT INTO contacts (first_name, last_name) VALUES ('$first_name', '$last_name')";
        } else {
            $sql = "UPDATE contacts SET first_name = '$first_name', last_name = '$last_name' WHERE id = $id";
        }
    } 
    else {
        $name = mysqli_real_escape_string($connect, $_POST['name']);
        $amount = floatval($_POST['amount']);
        $contact_id = intval($_POST['contact_id']);

        if ($action === 'create') {
            $sql = "INSERT INTO deals (name, amount, contact_id) VALUES ('$name', $amount, $contact_id)";
        } else {
            $sql = "UPDATE deals SET name = '$name', amount = $amount, contact_id = $contact_id WHERE id = $id";
        }
    }
    if (mysqli_query($connect, $sql)) {
        header("Location: ?type=$type");
        exit;
    } else {
        echo "Ошибка: " . mysqli_error($connect);
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="style.css">
    <title>Тестовое задание</title>

</head>
<body>
    <div id="menu">
        <h2>Меню</h2>
        <ul>
            <li><a href="?type=deals" class="<?= $type === 'deals' ? 'selected' : '' ?>">Сделки</a></li>
            <li><a href="?type=contacts" class="<?= $type === 'contacts' ? 'selected' : '' ?>">Контакты</a></li>
        </ul>
    </div>
    <div id="list">
        <h2><?= $type === 'contacts' ? 'Контакты' : 'Сделки' ?></h2>
        <ul>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <li>
                    <a href="?type=<?= $type ?>&action=view&id=<?= $row['id'] ?>">
                        <?= $type === 'contacts' ? "{$row['first_name']} {$row['last_name']}" : "{$row['name']} (ID: {$row['id']})" ?>
                    </a>
                </li>
            <?php endwhile; ?>
        </ul>
        <a href="?type=<?= $type ?>&action=create">Добавить</a>
    </div>

    <div id="content">
        <?php if ($action === 'view' && $id !== null): ?>
            <?php
            if ($type === 'contacts') {
                $sql = "SELECT * FROM contacts WHERE id = $id";
            } else {
                $sql = "SELECT deals.*, contacts.first_name, contacts.last_name 
                        FROM deals 
                        LEFT JOIN contacts ON deals.contact_id = contacts.id 
                        WHERE deals.id = $id";
            }
            $result = mysqli_query($connect, $sql);
            $row = mysqli_fetch_assoc($result);
            ?>
            <h2>Просмотр <?= $type === 'contacts' ? 'Контакта' : 'Сделки' ?></h2>
            <table>
                <?php if ($type === 'contacts'): ?>
                    <tr><th>Имя:</th><td><?= htmlspecialchars($row['first_name']) ?></td></tr>
                    <tr><th>Фамилия:</th><td><?= htmlspecialchars($row['last_name']) ?></td></tr>
                <?php else: ?>
                    <tr><th>Наименование:</th><td><?= htmlspecialchars($row['name']) ?></td></tr>
                    <tr><th>Сумма:</th><td><?= htmlspecialchars($row['amount']) ?></td></tr>
                    <tr><th>Контакт:</th><td><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></td></tr>
                <?php endif; ?>
            </table>
            <a href="?type=<?= $type ?>&action=edit&id=<?= $id ?>">Редактировать</a> |
            <a href="?type=<?= $type ?>&action=delete&id=<?= $id ?>" onclick="return confirm('Удалить этот элемент?')">Удалить</a>
        <?php elseif ($action === 'create' || ($action === 'edit' && $id !== null)): ?>
            <?php
            $first_name = $last_name = $name = $amount = $contact_id = '';
            if ($action === 'edit') {
                if ($type === 'contacts') {
                    $sql = "SELECT * FROM contacts WHERE id = $id";
                } else {
                    $sql = "SELECT * FROM deals WHERE id = $id";
                }
                $result = mysqli_query($connect, $sql);
                $row = mysqli_fetch_assoc($result);

                if ($type === 'contacts') {
                    $first_name = $row['first_name'];
                    $last_name = $row['last_name'];
                } else {
                    $name = $row['name'];
                    $amount = $row['amount'];
                    $contact_id = $row['contact_id'];
                }
            }
            ?>
            <h2><?= $action === 'create' ? 'Создание' : 'Редактирование' ?> <?= $type === 'contacts' ? 'Контакта' : 'Сделки' ?></h2>
            <form method="post" action="?type=<?= $type ?>&action=<?= $action ?>&id=<?= $id ?>">
                <?php if ($type === 'contacts'): ?>
                    <div>
                        <label for="first_name">Имя:</label>
                        <input type="text" id="first_name" name="first_name" value="<?= htmlspecialchars($first_name) ?>" required>
                    </div>
                    <div>
                        <label for="last_name">Фамилия:</label>
                        <input type="text" id="last_name" name="last_name" value="<?= htmlspecialchars($last_name) ?>">
                    </div>
                <?php else: ?>
                    <div>
                        <label for="name">Наименование:</label>
                        <input type="text" id="name" name="name" value="<?= htmlspecialchars($name) ?>" required>
                    </div>
                    <div>
                        <label for="amount">Сумма:</label>
                        <input type="text" id="amount" name="amount" value="<?= htmlspecialchars($amount) ?>" required>
                    </div>
                    <div>
                        <label for="contact_id">Контакт:</label>
                        <select id="contact_id" name="contact_id" required>
                            <?php
                            $contacts_sql = "SELECT id, first_name, last_name FROM contacts";
                            $contacts_result = mysqli_query($connect, $contacts_sql);
                            while ($contact = mysqli_fetch_assoc($contacts_result)):
                                $selected = $contact['id'] == $contact_id ? 'selected' : '';
                                ?>
                                <option value="<?= $contact['id'] ?>" <?= $selected ?>>
                                    <?= $contact['first_name'] . ' ' . $contact['last_name'] ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                <?php endif; ?>
                <div>
                    <button type="submit"><?= $action === 'create' ? 'Создать' : 'Сохранить' ?></button>
                    <a href="?type=<?= $type ?>">Отмена</a>
                </div>
            </form>
        <?php elseif ($action === 'delete' && $id !== null): ?>
            <?php
            if ($type === 'contacts') {
                $sql = "DELETE FROM contacts WHERE id = $id";
            } else {
                $sql = "DELETE FROM deals WHERE id = $id";
            }
            if (mysqli_query($connect, $sql)) {
                echo "<p>Элемент удалён.</p>";
            } else {
                echo "<p>Ошибка удаления элемента: " . mysqli_error($connect) . "</p>";
            }
            ?>
            <a href="?type=<?= $type ?>">Вернуться к списку</a>
        <?php endif; ?>
    </div>
</body>
</html>
