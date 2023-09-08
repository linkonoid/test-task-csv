<?php

/**
 * @author Max Barulin (https://github.com/linkonoid)
 */
	
	// Подключаем Composer-библиотеки
	require_once 'vendor/autoload.php';

	// Подключаем собственные классы для работы с данными
	spl_autoload_register(function ($class_name) {
		$class_name_arr = explode('\\', $class_name);
		$class_name = mb_strtolower(implode('/', $class_name_arr));
    	require_once realpath(__DIR__ . '/' . $class_name . '.php');
	});

	// Парсим файл конфигурации
	$config = \Symfony\Component\Yaml\Yaml::parseFile('./config.yaml');
	//var_dump($config);

	// Подключаем базу данных
	$db = new \Classes\Db($config['db']);

	// Инициализируем Twig
	$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/templates');
	$twig = new \Twig\Environment($loader,
		[
		    'cache' => $config['twig']['cache'] ? __DIR__ . $config['twig']['cache_folder'] : false,
		]
	);

	//Определяем базовые переменные для Twig (меню)
	$twig_vars = [
		'menuItems' => [
			'Index page' => '/',
			'CSV Upload' => '/upload',
			'Price' => "/price"
		]
	];

	$implement = new stdClass();
	$implement->config = $config;
	$implement->db = $db;
    $implement->twig = $twig;
	$implement->twig_vars = $twig_vars;

	// Определяем роуты по данным из файла конфигурации
	$router = new \Classes\Router($implement);
	foreach ($config['routes'] as $route)
	{
		$router->route($route['method'], $route['route'], '../' . $route['path']);
	}

	//Закрываем соединения с базой
	$this->db->mysqlClose();
