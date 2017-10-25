<?php
namespace Programulin\Validation;

use Programulin\Validation\Exceptions\NotReadableFile;

class FileValidator
{
	private $rules;
	private $file = [];
	private $errors = [];

	/**
	 * Установка правил валидации.
	 * 
	 * @param array $rules
	 * @return $this
	 */
	public function rules(array $rules)
	{
		if(isset($rules['width']) and strpos($rules['width'], ':'))
		{
			$width = explode(':', $rules['width']);
			$rules['min_width'] = $width[0];
			$rules['max_width'] = $width[1];
			unset($rules['width']);
		}

		if(isset($rules['height']) and strpos($rules['height'], ':'))
		{
			$height = explode(':', $rules['height']);
			$rules['min_height'] = $height[0];
			$rules['max_height'] = $height[1];
			unset($rules['height']);
		}
		
		$img_rules = ['height', 'min_height', 'max_height', 'width', 'min_width', 'max_width'];
		
		foreach($img_rules as $img_rule)
		{
			if(isset($rules[$img_rule]))
			{
				$rules['image'] = true;
				break;
			}
		}
		
		$this->rules = $rules;
		return $this;
	}

	/**
	 * Указание пути к валидируемому файлу.
	 * 
	 * @param type $path
	 * @return $this
	 */
	public function path($path)
	{
		$this->file = [
			'path' => $path,
			'ext'  => pathinfo($path, PATHINFO_EXTENSION),
		];

		return $this;
	}

	/**
	 * Указание информации о загруженном файле (например $_FILES['file']).
	 * 
	 * @param array $file
	 * @return $this
	 */
	public function file(array $file)
	{
		$this->file = [
			'path' => $file['tmp_name'],
			'ext'  => pathinfo($file['name'], PATHINFO_EXTENSION),
			'info' => $file,
		];

		return $this;
	}

	/**
	 * Валидация файла.
	 * 
	 * @return boolean
	 * @throws NotReadableFile
	 */
	public function validate()
	{
		// Проверка статуса загрузки файла
		if (isset($this->file['info']) and $this->file['info']['error'] > 0)
		{
			if($this->file['info']['error'] < 3)
				$this->errors[] = "Размер файла превышает {$this->rules['weight']} Мб.";
			else
				$this->errors[] = "Ошибка №{$this->file['info']['error']}, обратитесь к разработчику.";

			return false;
		}

		// Проверка доступности файла
		if (!is_readable($this->file['path']))
			throw new NotReadableFile('Файл не существует или недоступен для чтения.');
		
		// Проверка корректности изображения
		if (!empty($this->rules['image']))
		{
			$img_info = @getimagesize($this->file['path']);
			
			if (!$img_info)
			{
				$this->errors[] = 'Файл не является корректным изображением.';
				return false;
			}

			if (isset($this->rules['min_width']) and $img_info[0] < $this->rules['min_width'])
				$this->errors[] = "Минимальная ширина изображения {$this->rules['min_width']}px";

			if (isset($this->rules['width']) and $img_info[0] !== $this->rules['width'])
				$this->errors[] = "Ширина изображения должна быть {$this->rules['width']}px";

			if (isset($this->rules['max_width']) and $img_info[0] > $this->rules['max_width'])
				$this->errors[] = "Максимальная ширина изображения {$this->rules['max_width']}px";

			if (isset($this->rules['min_height']) and $img_info[1] < $this->rules['min_height'])
				$this->errors[] = "Минимальная высота изображения {$this->rules['min_height']}px";
				
			if (isset($this->rules['height']) and $img_info[1] !== $this->rules['height'])
				$this->errors[] = "Высота изображения должна быть {$this->rules['height']}px";
				
			if (isset($this->rules['max_height']) and $img_info[1] > $this->rules['max_height'])
				$this->errors[] = "Максимальная высота изображения {$this->rules['max_height']}px";
		}

		// Проверка размера
		if (filesize($this->file['path']) > $this->rules['weight'] * 1024 * 1024)
			$this->errors[] = "Размер изображения не должен превышать {$this->rules['weight']} Мб";
			
		// Проверка расширения
		if (!empty($this->rules['extensions']) and !in_array($this->file['ext'], $this->rules['extensions'], true))
			$this->errors[] = 'Некорректное расширение файла. Разрешены: ' . implode(', ', $this->rules['extensions']);
		
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
	 * Проверка, отправил ли юзер файл на сервер.
	 * 
	 * @return bool
	 */
	public function isSended()
	{
		return isset($this->file['info']) and $this->file['info']['error'] !== 4;
	}
}
