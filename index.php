<?php
require_once 'app/Application.php';
$app = new Application();

$date = $_GET['date'] ?? null;
$app->getData($date);

$table = $app->getTable();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <title><?= $app->getTitlePage() ?></title>

    <link href="/assets/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link href="/assets/css/style.css" rel="stylesheet" crossorigin="anonymous">

    <meta name="theme-color" content="#712cf9">
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <main class="col px-md-4">
            <div class="mb-5 chart-wrapper">
                <figure class="highcharts-figure">
                    <div id="container"></div>
                </figure>

                <a href="?date=<?= Application::DATE_TYPE_MONTH ?>" class="btn btn-lg
                    <?= $date === Application::DATE_TYPE_MONTH || $date === '' ? 'btn-primary' : 'btn-secondary' ?>"
                   role="button">Month</a>
                <a href="?date=<?= Application::DATE_TYPE_WEEK ?>" class="btn btn-lg
                    <?= $date === Application::DATE_TYPE_WEEK ? 'btn-primary' : 'btn-secondary' ?>"
                   role="button">Week</a>
                <a href="?date=<?= Application::DATE_TYPE_DATE ?>" class="btn btn-lg
                    <?= $date === Application::DATE_TYPE_DATE ? 'btn-primary' : 'btn-secondary' ?>"
                   role="button">Day</a>
            </div>

            <h2>Section title</h2>
            <div class="table-responsive">
                <table class="table table-striped table-sm">
                    <thead>
                    <tr>
                        <th scope="col">Period</th>
                        <th scope="col">Min</th>
                        <th scope="col">Max</th>
                        <th scope="col">Avg</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($table as $val): ?>
                        <tr>
                            <td><?= $val['period'] ?? '' ?></td>
                            <td><?= $val['min_value'] ?? '' ?></td>
                            <td><?= $val['max_value'] ?? '' ?></td>
                            <td><?= $val['avg_value'] ?? '' ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</div>

<script src="/assets/js/highcharts.js"></script>
<script src="/assets/js/highcharts-more.js"></script>
<script src="/assets/js/exporting.js"></script>
<script src="/assets/js/export-data.js"></script>
<script src="/assets/js/accessibility.js"></script>

<script>
    let ranges = <?= json_encode($app->getRanges())?>;
    let averages = <?= json_encode($app->getAverages())?>;

    Highcharts.chart('container', {
        title: {
            text: 'SMA Temperatures'
        },

        xAxis: {
            type: 'datetime',
        },

        yAxis: {
            title: {
                text: null
            }
        },

        tooltip: {
            crosshairs: true,
            shared: true,
            valueSuffix: '°C'
        },

        series: [{
            name: 'Temperature',
            data: averages,
            zIndex: 1,
            marker: {
                fillColor: 'white',
                lineWidth: 2,
                lineColor: Highcharts.getOptions().colors[0]
            }
        }, {
            name: 'Range',
            data: ranges,
            type: 'arearange',
            lineWidth: 0,
            linkedTo: ':previous',
            color: Highcharts.getOptions().colors[0],
            fillOpacity: 0.3,
            zIndex: 0,
            marker: {
                enabled: false
            }
        }]
    });
</script>
<script src="/assets/js/bootstrap.bundle.min.js"
        crossorigin="anonymous"></script>
</body>
</html>
