<?php
require_once 'app/CsvParser.php';

class Application
{
    private const DB_CONFIG_HOST = 'postgres'; //to config
    private const DB_CONFIG_PORT = 5432; //to config
    private const DB_CONFIG_DB = 'tz_db'; //to config
    private const DB_CONFIG_USER = 'tzuser'; //to config
    private const DB_CONFIG_PSWD = 'HCK6LreUsVu63ZdF'; //to config

    public const DATE_TYPE_WEEK = 'week';
    public const DATE_TYPE_MONTH = 'month';
    public const DATE_TYPE_DATE = 'day';

    private const PERIOD_DAYS_WEEK = ['Sunday', 'Monday', 'Tuesday', 'Wednesday','Thursday','Friday', 'Saturday'];

    /**
     * @var PDO
     */
    private $pdo;
    private $avarages = [];
    private $ranges = [];
    private $table = [];

    public function __construct()
    {
        $this->dbConnect();
        $this->addWeatherData();
    }

    private function dbConnect(): void
    {
        $host = self::DB_CONFIG_HOST;
        $port = self::DB_CONFIG_PORT;
        $db = self::DB_CONFIG_DB;
        $user = self::DB_CONFIG_USER;
        $password = self::DB_CONFIG_PSWD;

        try {
            $dsn = "pgsql:host=$host;port=$port;dbname=$db;";
            $this->pdo = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        } catch (PDOException $e) {
            die($e->getMessage()); // to errors user frendly
        }
    }

    private function addWeatherData()
    {
        $count = $this->getTemperaturesCount();

        if ($count > 0) {
            return;
        }

        $csv = new CsvParser();
        $inserts = $csv->getData('temperatures.csv');

        foreach (array_chunk($inserts, 50) as $items) {
            $values = '';
            foreach ($items as $value) {
                $values .= '(\'' . $value['date'] . '\',' . $value['value'] . '),';
            }
            $sql = "INSERT INTO temperatures (date, value) values $values";
            $this->pdo->query(substr($sql, 0, -1));
        }
    }

    public function getTemperaturesCount()
    {
        return $this->pdo->query('select count(*) from temperatures')->fetchColumn();
    }

    public function getTitlePage(): string
    {
        return 'Weather SMA Moscow 2021';
    }

    public function getRanges():array
    {
        return $this->ranges;
    }

    public function getAverages():array
    {
        return $this->avarages;
    }

    public function getTable():array
    {
        return $this->table;
    }

    public function getData(?string $dateType = self::DATE_TYPE_MONTH)
    {
        switch ($dateType) {
            case self::DATE_TYPE_MONTH:
            default:
                $sql = $this->getSqlByMonth();
                break;
            case self::DATE_TYPE_WEEK:
                $sql = $this->getSqlByWeek();
                break;
            case self::DATE_TYPE_DATE:
                $sql = $this->getSqlByDay();
                break;
        }

        $data = $this->pdo->query($sql)->fetchAll();

        foreach ($data as $temp) {
            $time = strtotime($temp['date_type']) * 1000;

            if ($time <= 0) {
                continue;
            }

            $avgValue = round($temp['avg_value'], 2);

            switch ($dateType) {
                case self::DATE_TYPE_MONTH:
                default:
                    $period = date('m.Y', strtotime($temp['date_type']));
                    break;
                case self::DATE_TYPE_WEEK:
                    $period = self::PERIOD_DAYS_WEEK[ date("w", strtotime($temp['date_type']) )];
                    break;
                case self::DATE_TYPE_DATE:
                    $period = date('d.m.Y', strtotime($temp['date_type']));
                    break;
            }

            $this->ranges[] = [
                $time,
                $temp['min_value'],
                $temp['max_value'],
            ];

            $this->avarages[] = [
                $time,
                $avgValue
            ];

            $this->table[] = [
                'period' => $period,
                'min_value' => $temp['min_value'],
                'max_value' => $temp['max_value'],
                'avg_value' => $avgValue
            ];
        }
    }


    private function getSqlByMonth(): string
    {
        return <<<SQL
SELECT date_trunc('month', date) AS date_type,
       AVG(value) as avg_value,
       MIN(value) as min_value,
       MAX(value) as max_value
FROM temperatures
GROUP BY date_type
ORDER BY date_type;
SQL;

    }

    private function getSqlByWeek(): string
    {
        return <<<SQL
SELECT date_trunc('week', date) AS date_type,
       AVG(value) as avg_value,
       MIN(value) as min_value,
       MAX(value) as max_value
FROM temperatures
GROUP BY date_type
ORDER BY date_type;
SQL;

    }

    private function getSqlByDay(): string
    {
        return <<<SQL
SELECT date_trunc('day', date) AS date_type,
       AVG(value) as avg_value,
       MIN(value) as min_value,
       MAX(value) as max_value
FROM temperatures
GROUP BY date_type
ORDER BY date_type;
SQL;

    }
}