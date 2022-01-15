# Simple_Backup
Простой класс для создания бекапов 


#$CBackup = new CBackup('/path/for/backup');
создаем объект класса, передавая директорию в качестве аргумента

#$CBackup->setOutputFolder($_SERVER['DOCUMENT_ROOT'].'/backup/backups');
задаем директорию создания бекапа

$CBackup->setIgnoreDirs(['backup', 'no_bkp']); // передаем массив директорий, исключенных из бекапа (сюда можно передать имя папки с архивами бекапов)
$CBackup->useBackupDB(true); // указываем, что требуется сделать дамп базы
$CBackup->setDB('localhost', 'username', 'password', 'db_name'); // задаем параметры полкдючения к базе данных
$CBackup->setTTL(5); // указываем срок годности уже созданных архивов (если просрочены - удаляем)
$CBackup->deleteOldBackups(); // удаляем старые архивы

$res = $CBackup->create(); // запуск создания бекапа
if ($res) { // если true, то все создалось и можно посмотреть результат
    echo 'OK<br>';
} else {
    echo 'Fail. Error: ' . $CBackup->getLastError(); // если ошибка - то получаем текст ошибки
}


print_r($CBackup->getResult()); // получаем результат работы, в котором содержатся все данные (пути к бекапам, время работы и удаленные бекапы)
