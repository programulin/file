Валидатор файлов.
=====================

Пример работы с обычным файлом на сервере:

```php
use Programulin\Validation\FileValidator;

$validator = new FileValidator;

$validator->rules([
    'weight'     => 10, // Вес файла не более 10Мб
    'extensions' => ['jpg', 'jpeg', 'png', 'gif'], // Белый список расширений
    'width'      => 150, // Ширина изображения 150 пикселей
    'height'     => 150, // Высота изображения 150 пикселей
])
    ->path('file.png');

if($validator->validate())
    echo 'Ошибок не обнаружено';
else
    print_r($validator->errors());
```

Пример работы с файлом, загруженным по HTTP:
```php
use Programulin\Validation\FileValidator;

$validator = new FileValidator;

$validator->rules([
    'weight'     => 10,
    'extensions' => ['jpg', 'jpeg', 'png', 'gif'],
    'width'      => 150,
    'height'     => 150,
])
    ->file($_FILES['file']);

// Проверка, что пользователь отправил файл ($_FILES['file']['error'] !== 4)
if($validator->isSended())
{
    if($validator->validate())
        echo 'Ошибок не обнаружено';
    else
        print_r($validator->errors());
}
else
{
    echo 'Вы не выбрали файл.';
}
```

Требования:
-----------------------------------
- PHP 5.4+
- Расширение GD

Установка с помощью Composer:
-----------------------------------

```json
{
    "require":{
        "programulin/file-validator": "dev-master"
    },
    "repositories":[
        {
            "type":"github",
            "url":"https://github.com/programulin/file-validator"
        }
    ]
}
```

### rules

Устанавливает правила валидации. В данный момент доступны следующие правила:

```php
$validator->rules([
    'weight'     => 10, // Размер файла не более 10Мб
    'extensions' => ['jpg', 'jpeg', 'png', 'gif'], // Белый список расширений

    'width'      => 150, // Ширина в пикселях
    'width'      => '150:300', // Ширина от 150 до 300 пикселей
    'min_width'  => 150, // Ширина от 150 пикселей
    'max_width'  => 150, // Ширина до 150 пикселей

    'height'     => 150, // Высота в пикселях
    'height'     => '150:300', // Высота от 150 до 300 пикселей
    'min_height'  => 150, // Высота от 150 пикселей
    'max_height'  => 150, // Высота до 150 пикселей

    /*
        Проверка, является ли файл изображением. Имеет смысл только если вам не нужно проверять размеры,
        поскольку наличие любого width/height правила переключает этот параметр в true.
    */
    'image' => true
]);
```

### path

Устанавливает файл для валидации.

```php
$validator->path('somefile.pdf');
```

### file

Устанавливает параметры файла, загруженного по HTTP:

```php
$validator->file($_FILES['somefile']);
```

В отличие от path(), этот метод валидирует расширение в исходном имени файла ($_FILES['file']['name']), а также валидирует $_FILES['file']['error'].

### isSended
Позволяет проверить, был ли отправлен файл ($_FILES['somefile']['error'] !== 4):

```php
if($validator->isSended())
{
    // ...
}
```

Имеет смысл только при установке файла через метод file().

### validate

Валидирует изображение и возвращает результат (true/false).

### errors

Возвращает массив ошибок.

ChangeLog:
-----------------------------------

### 1.0.0
- Документация с примерами.
- Упрощена установка правил, теперь есть только один метод rules().