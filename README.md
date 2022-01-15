# Simple_Backup
Простой класс для создания бекапов.<br>
Позволяет создавать архив с файловым бекапом, а так же делать дамп базы данных.<br>
При необходимости можно исключить некоторые директории из бекапа (такие как папка с бекапами или медиа)<br>
Также считает время выполнения каждой операции, что даст более детальное понимание процесса.<br>
Помимо этого предусмотрена чистка директории бекапов от старых данных (время актуальности настраивается), что позволяет экономить дисковое пространство.<br>


### $CBackup = new CBackup('/path/for/backup');
Создаем объект класса, передавая директорию в качестве аргумента

### $CBackup->setOutputFolder($_SERVER['DOCUMENT_ROOT'].'/backup/backups');
Задаем директорию создания бекапа

### $CBackup->setIgnoreDirs(['backup', 'no_bkp']);
Передаем массив директорий, исключенных из бекапа (сюда можно передать имя папки с архивами бекапов). Необязательный

### $CBackup->useBackupDB(true);
Указываем, что требуется сделать дамп базы. Необязательный

### $CBackup->setDB('localhost', 'username', 'password', 'db_name');
Задаем параметры полкдючения к базе данных. Обязательный при указании создании дампа базы. Иначе необязательный

### $CBackup->setTTL(5);
Указываем срок годности уже созданных архивов в днях. Необязательный, по умолчанию 5

### $CBackup->deleteOldBackups(); 
Удаляем старые бекапы

### $CBackup->create(); 
Запуск создания бекапа. В случае ошибки вернет false 

### $CBackup->getLastError(); 
Получаем текст ошибки

### $CBackup->getResult();
Получаем результат работы, в котором содержатся все данные (пути к бекапам, время работы и удаленные бекапы). Возвращает ассоциативный массив
