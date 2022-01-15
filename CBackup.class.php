<?php

/**
 * Класс для создания бекапов
 *
 * @author Roman Grinko <rsgrinko@gmail.com>
 */
class CBackup
{
    /**
     * @var string $folder Директория бекапирования
     */
    private string $folder;

    /**
     * @var string $outputFolder Директория сохранения бекапа
     */
    private string $outputFolder = '';

    /**
     * @var array $ignoreDirs Исключенные из бекапа директории
     */
    private array $ignoreDirs = [];

    /**
     * @var int $ttl Время в днях, в течении которого бекап считается актуальным
     */
    private int $ttl = 5;

    /**
     * @var int $startTime Время выполнения
     */
    private int $startTime = 0;

    /**
     * @var bool $useBackupDB Флаг бекапа базы данных
     */
    private bool $useBackupDB = false;

    /**
     * @var string $dbHost Сервер базы данных
     */
    private string $dbHost;

    /**
     * @var string $dbUser Пользователь базы данных
     */
    private string $dbUser;

    /**
     * @var string $dbName Имя базы данных
     */
    private string $dbName;

    /**
     * @var string $dbPassword Пароль пользователя базы данных
     */
    private string $dbPassword;

    /**
     * @var string $backupPrefix Префикс к бекапу
     */
    private string $backupPrefix = '';

    /**
     * @var string $lastError Текст последней ошибки
     */
    private ?string $lastError = null;

    /**
     * @var array Результат работы скрипта
     */
    private array $backupResult = [
        'create' => [
            'files' => ['path' => '', 'time' => ''],
            'db' => ['path' => '', 'time' => '']
        ],
        'remove' => ['files' => [], 'time' => '']
    ];

    /**
     * Конструктор класса
     *
     * @param string $folder Директория бекапирования
     */
    public function __construct(string $folder)
    {
        $this->folder = $folder;
    }

    /**
     * Получить время работы скрипта
     *
     * @return string
     */
    private function getExecutionTime(): string
    {
        $finish_time = microtime(true);
        $delta = round($finish_time - $this->startTime, 3);
        if ($delta < 0.001) {
            $delta = 0.001;
        }
        return $delta . ' cek.';
    }

    /**
     * Устанавливаем срок годности бекапов
     *
     * @param int $days Количество дней
     */
    public function setTTL(int $days) {
        $this->ttl = $days;
    }

    /**
     * Сброс времени работы скрипта
     */
    private function resetTime(): void
    {
        $this->startTime = microtime(true);
    }

    /**
     * Установка директории сохранения бекапа
     *
     * @param string $folder Директория
     */
    public function setOutputFolder(string $folder): void
    {
        $this->outputFolder = $folder;
    }

    /**
     * Установка исключенных из бекапа директорий
     *
     * @param array $arDirs Массив названий директорий
     */
    public function setIgnoreDirs(array $arDirs): void
    {
        $this->ignoreDirs = $arDirs;
    }

    /**
     * Включение БД в бекап
     *
     * @param bool $useDBBackup Флаг включения бекапа базы
     */
    public function useBackupDB(bool $useDBBackup): void
    {
        $this->useBackupDB = $useDBBackup;
    }

    /**
     * Установка параметров БД
     *
     * @param string $host Сервер
     * @param string $user Имя пользователя
     * @param string $password Пароль
     * @param string $base База
     */
    public function setDB(string $host, string $user, string $password, string $base): void
    {
        $this->dbHost = $host;
        $this->dbUser = $user;
        $this->dbPassword = $password;
        $this->dbName = $base;
    }

    /**
     * Получение текста последней ошибки
     *
     * @return string|null
     */
    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    /**
     * Создание файлового бекапа
     */
    private function backupFiles(): void
    {
        $this->resetTime();
        $fullFileName = $this->outputFolder . '/' . $this->backupPrefix . 'files.tar.gz';
        $excludeString = '';
        if (!empty($this->ignoreDirs)) {
            foreach ($this->ignoreDirs as $ignoreDir) {
                $excludeString .= " --exclude='" . $ignoreDir . "'";
            }
        }
        shell_exec('tar -cvf ' . $fullFileName . ' -C ' . $this->folder . ' ' . $excludeString . ' .');
        $this->backupResult['create']['files']['path'] = $fullFileName;
        $this->backupResult['create']['files']['time'] = $this->getExecutionTime();
    }

    /**
     * Создание дампа базы
     */
    private function backupDB(): void
    {
        $this->resetTime();
        $fullFileName = $this->outputFolder . '/' . $this->backupPrefix . 'db.sql';
        shell_exec('mysqldump -h' . $this->dbHost . ' -u' . $this->dbUser . ' -p' . $this->dbPassword . ' ' . $this->dbName . ' > ' . $fullFileName);
        $this->backupResult['create']['db']['path'] = $fullFileName;
        $this->backupResult['create']['db']['time'] = $this->getExecutionTime();
    }

    /**
     * Создание бекапа
     *
     * @return bool
     */
    public function create(): bool
    {
        if (empty($this->folder) || $this->folder == '') {
            $this->lastError = 'Не задана директория для бекапа';
            return false;
        }
        if (empty($this->outputFolder)) {
            $this->lastError = 'Не задана директория сохранения бекапа';
            return false;
        }

        if ($this->backupDB && (empty($this->dbName) || empty($this->dbPassword) || empty($this->dbUser) || empty($this->dbHost))) {
            $this->lastError = 'Не заданы параметры подключения к базе';
            return false;
        }

        $this->backupPrefix = 'backup_' . date("d.m.Y_H-i-s") . '_';
        $this->backupFiles();
        if ($this->useBackupDB) {
            $this->backupDB();
        }
        return true;
    }

    /**
     * Удаление старых бекапов
     *
     * @return array|null
     */
    public function deleteOldBackups(): ?array
    {
        $this->resetTime();
        if (empty($this->outputFolder)) {
            $this->lastError = 'Не задана директория сохранения бекапа';
            return null;
        }

        $files = glob($this->outputFolder . "/*");

        $deleted = array();
        foreach ($files as $file) {
            if ((time() - filemtime($file)) > ($this->ttl * 86400)) {
                array_push($deleted, $file);
                unlink($file);
            }
        }
        $this->backupResult['remove']['files'] = $deleted;
        $this->backupResult['remove']['time'] = $this->getExecutionTime();
        return $deleted;
    }

    /**
     * Получение результата работы
     *
     * @return array
     */
    public function getResult(): array
    {
        return $this->backupResult;
    }
}
