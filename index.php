<?php
error_reporting(E_ALL);
include 'connection.php';

#region //Определение заголовков и стилей на странице
$fieldset_class = "main-container-fieldset";
$page = 0;
$calendar = "calendar-hidden";
if (!empty($_GET["page"])) {
    $page = $_GET["page"];
        if ($page == "1") {
            $calendar = "calendar-visible";
            $fieldset_class = "main-container-fieldset sub-page";
        }
}
#endregion

#region //Сокрытие блока добавления записи
$add_allowence = false;
$add_form_visibility = "visible";
$add_form_class = "main-container-fieldset__add-form-hidden";
if (isset($_POST['add_form_visibility'])) {
    $add_form_visibility = $_POST['add_form_visibility'];
    if ($add_form_visibility == "visible") {
        $add_form_class = "main-container-fieldset__add-form-visible";
        $add_form_visibility = "visible-act";
    } else if ($add_form_visibility == "visible-act"){
        $add_form_class = "main-container-fieldset__add-form-hidden";
        $add_form_visibility = "visible";
        $add_allowence = true;
    } else {
        $add_form_class = "main-container-fieldset__add-form-hidden";
        $add_form_visibility = "visible";
    }
}
#endregion

#region //Добавление записи
if ($add_allowence == true && isset($_POST['description'])) {
    $description = (string)$_POST['description'];
    $last_id = mysqli_insert_id($sql_link);
    $date = date('Y-m-d h:i:s');
    $sql = "INSERT INTO tasks (id, description, is_done, date_added) VALUES ('$last_id', ?, 0, '$date')";
    $statement = $pdo->prepare($sql);
    $statement->execute(["{$description}"]);
}
#endregion

#region //Запрос на изменение записи
if (isset($_POST['change'], $_POST['description'], $_GET['id'])) {
    $description = (string)$_POST['description'];
    $id = $_GET['id'];
    $sql = "UPDATE tasks SET description = ? WHERE id = '$id'";
    $statement = $pdo->prepare($sql);
    $statement->execute(["{$description}"]);
}
#endregion

#region //Запросы на изменение статуса записей в таблице
if (isset($_GET['status'], $_GET['id'])) {
    $status = (int)$_GET['status'];
    $id = (int)$_GET['id'];
    if ($status == 0) {
        $sql = "UPDATE tasks SET is_done = '1' WHERE id = ?";
        $statement = $pdo->prepare($sql);
        $statement->execute(["{$id}"]);
    } else {$id
        $sql = "UPDATE tasks SET is_done = '0' WHERE id = ?";
        $statement = $pdo->prepare($sql);
        $statement->execute(["{$id}"]);
    }
}
#endregion

#region //Удаление записи из таблицы
if (isset($_GET['id'], $_GET['action']) && $_GET['action']=='delete') {
    $id = (int)$_GET['id'];
    $sql = "DELETE FROM tasks WHERE id = ?";
    $statement = $pdo->prepare($sql);
    $statement->execute(["{$id}"]);
}
#endregion

#region //Сортировки по трем полям
$sort_type = "asc";
$sort_field = null;
if (isset($_GET['sort'], $_GET['field'])) {
    $sort_type = $_GET['sort'];
    $sort_field = (string)$_GET['field'];
    if ($sort_type == "asc") {
        $sql = "SELECT * FROM tasks ORDER BY ? ASC";
        $statement = $pdo->prepare($sql);
        $statement->execute(["{$sort_field}"]);
        $arrow = "<img src='image/up_sort.png' class='sort-arrow'";
        $sort_type = "desc";
    } elseif ($sort_type == "desc") {
        $sql = "SELECT * FROM tasks ORDER BY ? DESC";
        $statement = $pdo->prepare($sql);
        $statement->execute(["{$sort_field}"]);
        $arrow = "<img src='image/down_sort.png' class='sort-arrow'";
        $sort_type = "asc";
    }
} else {
#endregion
#region //Запрос на выборку из таблицы если не выбрана сортировка
    $sql = "SELECT * FROM tasks";
    $statement = $pdo->prepare($sql);
    $statement->execute();
}
#endregion

?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Cache-Control" content="no-cache">
    <link rel="shortcut icon" href="image/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" type="text/css" href="style.css">
    <title>TODOO</title>
</head>
<body>
<header class="header-container">
        <ul class="header-container__menu clearfix">
            <li class="header-container__menu__item"><img src="image/arrow.png" width="70" height="70"></li>
            <li class="header-container__menu__item"><a class="header-container-link logo-link" href="index.php?page=0">TODOO</a></li>
            <li class="header-container__menu__item"><a class="header-container-link" href="index.php?page=0">Main</a></li>
            <li class="header-container__menu__item"><a class="header-container-link" href="index.php?page=1">My tasks</a></li>
            <li class="header-container__menu__item <?= $calendar; ?>"><a href=""><img src="image/calendar.png" width="70" height="70"></a></li>
            <li class="header-container__menu__item log-reg-item"><a class="header-container-link log-reg-item" href="">Log In</a></li>
        </ul>
</header>
<hr class="horizontal-line">
<div class="main-container">
    <?php switch ($page) {
        case 0: ?>
    <fieldset class="<?= $fieldset_class; ?>">
        <h1 class="main-container-fieldset__text-h1"><span class="blue-color-span">TODOO</span> App</h1>
        <h3 class="main-container-fieldset__text-h3">Plan your life - remember all</h3>
        <h3 class="main-container-fieldset__text-h3 arrow-text">Start!</h3>
        <p class="p-center"><a href="index.php?page=1"><img src="image/down-arrow.png" class="main-container-fieldset__arrow"></a></p>
    </fieldset>
            <?php break; case 1: ?>
    <fieldset class="<?= $fieldset_class; ?>">
       <h2 class="main-container-fieldset__text-h2">My tasks</h2>
        <form method="POST" action="index.php?page=1" class="main-container-fieldset__button-form clearfix" >
            <div class="<?= $add_form_class; ?>">
                <input type="text" name="description" placeholder=" Task description" class="main-container-fieldset__input-text">
                <button class="button hide-button" name="add_form_visibility" value="hidden">Hide</button>
            </div>
            <button class="button add-button" name="add_form_visibility" value="<?= $add_form_visibility; ?>">+ADD</button>
        </form>
        <table class="main-container-table">
            <tr class="table-row">
                <td class="table-cell table-header first-column"><a class="main-container-table__header-link" href="?page=1&sort=<?= $sort_type; ?>&field=description">Description<?php if ($sort_field == 'description') echo $arrow; ?></a></td>
                <td class="table-cell table-header second-column"><a class="main-container-table__header-link" href="?page=1&sort=<?= $sort_type; ?>&field=is_done">Status<?php if ($sort_field == 'is_done') echo $arrow; ?></a></td>
                <td class="table-cell table-header third-column"><a class="main-container-table__header-link" href="?page=1&sort=<?= $sort_type; ?>&field=date_added">Add date<?php if ($sort_field == 'date_added') echo $arrow; ?></a></td>
                <td class="table-cell table-header fourth-column">TO-DO</td>
            </tr>
            <?php foreach ($statement as $value) {
                $id = htmlspecialchars($value['id']);
                $description = htmlspecialchars($value['description']);
                if (htmlspecialchars($value['is_done'], ENT_QUOTES) == 0) {
                    $is_done = 0;
                    $task_status_text = "normal-text";
                } else { $is_done = 1; $task_status_text = "obliterated-text"; } ?>
                <tr class="table-row">
                    <?php if (isset($_GET['id'], $_GET['action']) && $id == $_GET['id'] && $_GET['action'] == 'change') { ?>
                        <td class="table-cell first-column">
                            <form method="POST" action="index.php?page=1&id=<?= $id;?>">
                                <input type="text" name="description" value="<?= $description; ?>" class="main-container-fieldset__input-text__into-table">
                                <button class="button change-button" name="change">Submit</button>
                            </form>
                        </td>
                    <?php } else { ?>
                    <td class="table-cell first-column"><span class="<?= $task_status_text; ?>"><?= htmlspecialchars($value['description'], ENT_QUOTES); ?></span></td>
                    <?php } ?>
                    <td class="table-cell second-column">
                        <p class="p-center">
                            <a title="Нажмите чтобы изменить статус" href="?page=1&status=<?= $is_done;?>&id=<?= $id;?>"><?php if ($is_done == 0) {?> <img src="image/notdone.png" width="80" height="80"> <?php } else { ?> <img src="image/done.png" width="80" height="80"> <?php } ?>
                            </a>
                        </p>
                    </td>
                    <td class="table-cell third-column"><?= htmlspecialchars($value['date_added'], ENT_QUOTES); ?></td>
                    <td class="table-cell fourth-column">
                        <ul class="to-do-menu clearfix">
                            <li class="to-do-menu__item"><a class="to-do-menu__item-link" href="?page=1&id=<?= $id;?>&action=change">Change</a></li>
                            <li class="to-do-menu__item"><a class="to-do-menu__item-link" href="?page=1&id=<?= $id;?>&action=delete">Delete</a></li>
                        </ul>
                    </td>
                </tr>
            <?php } ?>
        </table>
    </fieldset>
            <?php break; } ?>
</div>
</body>
</html>
