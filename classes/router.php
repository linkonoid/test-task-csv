<?php namespace Classes;

/**
 * @author Max Barulin (https://github.com/linkonoid)
 */

use Exception;

class Router
{
	public $db = null;

	public $config = null;	

	public $twig = null;

	public $twig_vars = null;

    public function __construct($implement)
    {
		$this->db = $implement->db;
		$this->config = $implement->config;
		$this->twig = $implement->twig;
		$this->twig_vars = $implement->twig_vars;
    }

	public function route($method = "", $route = "", $path_to_include = "")
	{			

		$method = mb_strtoupper($method);

		if (in_array($method,['GET','POST','PUT','PATCH','ANY']))
		{
			$callback = $path_to_include;
			if (!is_callable($callback)) {
				if (!strpos($path_to_include, '.php')) {
					$path_to_include .= '.php';
				}
			}
			if ($route === "/404") {
				require_once __DIR__ . "/$path_to_include";
				exit;
			}

			if ($_SERVER['REQUEST_METHOD'] == $method)
			{
				$request_url = filter_var($_SERVER['REQUEST_URI'], FILTER_SANITIZE_URL);
				$request_url = rtrim($request_url, '/');
				$request_url = strtok($request_url, '?');
				$route_parts = explode('/', $route);
				$request_url_parts = explode('/', $request_url);
				array_shift($route_parts);
				array_shift($request_url_parts);
				if ($route_parts[0] == '' && count($request_url_parts) == 0) {
					// Callback function
					if (is_callable($callback)) {
						call_user_func_array($callback, []);
						exit;
					}
					require_once __DIR__ . "/$path_to_include";
					exit();
				}
				if (count($route_parts) != count($request_url_parts)) {
					require_once __DIR__ . "/views/404.php";
					exit;
				}
				$parameters = [];
				for ($__i__ = 0; $__i__ < count($route_parts); $__i__++) {
					$route_part = $route_parts[$__i__];
					if (preg_match("/^[$]/", $route_part)) {
						$route_part = ltrim($route_part, '$');
						array_push($parameters, $request_url_parts[$__i__]);
						$$route_part = $request_url_parts[$__i__];
					} else if ($route_parts[$__i__] != $request_url_parts[$__i__]) {
						return;
					}
				}
				// Callback function
				if (is_callable($callback)) {
					call_user_func_array($callback, $parameters);
					exit();
				}
				require_once __DIR__ . "/$path_to_include";
				exit;
			}
		}
	}

	public function twig_render($template, $vars)
	{
		$vars = array_merge($vars, $this->twig_vars);
		echo $this->twig->render($template, $vars);
	}

	public function out($text)
	{
		echo htmlspecialchars($text);
	}

	public function set_csrf()
	{
		session_start();
		if (!isset($_SESSION["csrf"])) {
			$_SESSION["csrf"] = bin2hex(random_bytes(50));
		}
		echo '<input type="hidden" name="csrf" value="' . $_SESSION["csrf"] . '">';
	}

	public function is_csrf_valid()
	{
		session_start();
		if (!isset($_SESSION['csrf']) || !isset($_POST['csrf'])) {
			return false;
		}
		if ($_SESSION['csrf'] != $_POST['csrf']) {
			return false;
		}
		return true;
	}
}
