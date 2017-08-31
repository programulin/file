<?php
namespace Programulin\File;

use Exception;

class Validator
{
    private $min_width;
    private $min_height;
    private $max_width;
    private $max_height;
    private $width;
    private $height;
    private $exts;
    private $is_img = false;
    private $size;
    private $path;
    private $ext;
    private $upload_error;
    private $errors = [];

    /**
     * Минимальная ширина изображения.
     * 
     * @param int $min_width
     * @return $this
     */
    public function minWidth($min_width)
    {
        $this->min_width = $min_width;
        $this->is_img = true;
        return $this;
    }

    /**
     * Фиксированная ширина изображения.
     * 
     * @param int $width
     * @return $this
     */
    public function width($width)
    {
        $this->width = $width;
        $this->is_img = true;
        return $this;
    }

    /**
     * Максимальная ширина изображения.
     * 
     * @param int $max_width
     * @return $this
     */
    public function maxWidth($max_width)
    {
        $this->max_width = $max_width;
        $this->is_img = true;
        return $this;
    }

    /**
     * Минимальная высота изображения.
     * 
     * @param int $min_height
     * @return $this
     */
    public function minHeight($min_height)
    {
        $this->min_height = $min_height;
        $this->is_img = true;
        return $this;
    }

    /**
     * Фиксированная высота изображения.
     * 
     * @param int $height
     * @return $this
     */
    public function height($height)
    {
        $this->height = $height;
        $this->is_img = true;
        return $this;
    }

    /**
     * Максимальная высота изображения.
     * 
     * @param int $max_height
     * @return $this
     */
    public function maxHeight($max_height)
    {
        $this->max_height = $max_height;
        $this->is_img = true;
        return $this;
    }

    /**
     * Максимальный размер файла.
     * 
     * @param int $size
     * @return $this
     */
    public function size($size)
    {
        $this->size = $size;
        return $this;
    }

    /**
     * Абсолютный путь к файлу.
     * 
     * @param string $path
     * @return $this
     */
    public function file($path)
    {
        $this->path = $path;
        $this->ext = pathinfo($path, PATHINFO_EXTENSION);
        return $this;
    }

    /**
     * Массив с информацией о загруженном файле, например $_FILES['file'].
     * 
     * @param array $file
     * @return $this
     */
    public function uploadedFile(array $file)
    {
        $this->path = $file['tmp_name'];
        $this->ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $this->upload_error = $file['error'];
        return $this;
    }

    /**
     * Массив разрешённых расширений файла.
     * 
     * @param array $exts
     * @return $this
     */
    public function exts(array $exts)
    {
        $this->exts = $exts;
        return $this;
    }

    /**
     * Валидация файла.
     * 
     * @return boolean
     * @throws Exception
     */
    public function validate()
    {        
        // Проверка статуса загрузки файла
        if ($this->upload_error > 0)
        {
            $errors = [
                1 => 'Размер файла превышает ' . ini_get('upload_max_filesize'),
                2 => 'Размер файла превышает локальное ограничение (MAX_FILE_SIZE)',
                3 => 'Файл не смог полностью загрузиться.',
                4 => 'Файл не выбран.',
                6 => 'Ошибка сервера. Отсутствует временная папка.',
                7 => 'Ошибка сервера. Не удалось записать файл.'
            ];

            if(isset($errors[$this->upload_error]))
            {
                $this->errors[] = $errors[$this->upload_error];
                return false;
            }
        }
        
        // Проверка существования файла
        if(!is_readable($this->path))
            throw new Exception('Файл не существует или недоступен для чтения.');

        // Проверка корректности изображения
        if($this->is_img)
        {
            $img_info = @getimagesize($this->path);

            if(!$img_info)
            {
                $this->errors[] = 'Файл не является корректным изображением.';
                return false;
            }

            if(isset($this->min_width) and $img_info[0] < $this->min_width)
                $this->errors[] = "Минимальная ширина изображения {$this->min_width}px";
            
            if(isset($this->width) and $img_info[0] !== $this->width)
                $this->errors[] = "Ширина изображения должна быть {$this->width}px";
            
            if(isset($this->max_width) and $img_info[0] > $this->max_width)
                $this->errors[] = "Максимальная ширина изображения {$this->max_width}px";
            
            if(isset($this->min_height) and $img_info[1] < $this->min_height)
                $this->errors[] = "Минимальная высота изображения {$this->min_height}px";

            if(isset($this->height) and $img_info[1] !== $this->height)
                $this->errors[] = "Высота изображения должна быть {$this->height}px";

            if(isset($this->max_height) and $img_info[1] > $this->max_height)
                $this->errors[] = "Максимальная высота изображения {$this->max_height}px";
        }

        // Проверка размера
        if (filesize($this->path) > $this->size * 1024 * 1024)
            $this->errors[] = "Размер изображения не должен превышать {$this->size} Мб";

        // Проверка расширения
        if(!empty($this->exts) and !in_array($this->ext, $this->exts, true))
            $this->errors[] = 'Некорректное расширение файла. Разрешены: ' . implode(', ', $this->exts);

        return empty($this->errors);
    }

    /**
     * Получение массива ошибок.
     * 
     * @return array
     */
    public function errors()
    {
        return $this->errors;
    }

    /**
     * Проверка, отправил ли юзер файл на сервер
     * 
     * @return bool
     */
    public function isSended()
    {
        return $this->upload_error !== 4;
    }
}
