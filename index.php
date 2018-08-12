<?php
header('charset=utf-8');
require_once ('autoload.php');


if (!isset($_POST['address']))
{
    echo 'Введите адрес';
    $_POST['address'] = NULL;
}

if (!isset($_GET['address']) && !isset($_GET['coordinates']))
{
    $_GET['coordinates'] = '55.751574, 37.573856';
    $_GET['address'] = 'Москва';
}

$api = new \Yandex\Geo\Api();
$api->setQuery($_POST['address']);

// Настройка фильтров
$api
    ->setLimit(50) // кол-во результатов
    ->setLang(\Yandex\Geo\Api::LANG_US) // локаль ответа
    ->load();

$response = $api->getResponse();
$response->getFoundCount(); // кол-во найденных адресов
$response->getQuery(); // исходный запрос
$response->getLatitude(); // широта для исходного запроса
$response->getLongitude(); // долгота для исходного запроса

// Список найденных точек
$collection = $response->getList();
foreach ($collection as $item) {
    $item->getAddress(); // вернет адрес
    $item->getLatitude(); // широта
    $item->getLongitude(); // долгота
    $item->getData(); // необработанные данные
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">

    <script src="https://api-maps.yandex.ru/2.1/?lang=ru_RU" type="text/javascript">
    </script>

    <script type="text/javascript">
        ymaps.ready(init);

        function init(){
            var myMap = new ymaps.Map("map", {
                center: [<?php echo $_GET['coordinates']; ?>],
                zoom: 7
            });

            var myPlacemark = new ymaps.Placemark([<?php echo $_GET['coordinates']; ?>], {
                hintContent: <?php echo json_encode($_GET['address']); ?>,
            });

            myMap.geoObjects.add(myPlacemark);
        }
    </script>

</head>
<body>

<form action="" method="POST">
    <input type="text" name="address" placeholder="Адрес" value="<?php if (!empty($_POST['address'])) echo $_POST['address']; ?>">
    <button type="submit" name="table" value="findAddress" />Найти</button>
</form>

<div id="map" style="width: 600px; height: 400px"></div>

<h3>Список возможных координатов</h3>

<?php if (isset($collection)):?>
<table border="1">
    <thead>
    <th>Адрес</th>
    <th>Координаты</th>
    <th>Найти на карте</th>
    </thead>
    <?php foreach ($collection as $item):?>
        <tbody>
        <tr>
            <td><?= $item->getAddress();?></td>
            <td><?= $item->getLatitude();?>, <?= $item->getLongitude();?></td>
            <td><a href="./index.php?address=<?php echo urlencode($item->getAddress()); ?>&amp;coordinates=<?php echo $item->getLatitude() .', '.$item->getLongitude(); ?>">Click here</a></td>
        </tr>
        </tbody>
        <?php endforeach ?>
</table>
<?php endif ?>

</body>
</html>