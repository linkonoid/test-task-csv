<?php namespace Classes;

/**
 * @author Max Barulin (https://github.com/linkonoid)
 */

class Db
{
	public $connect = null;

    public $config = null;	

    public function __construct($config)
    {
		$this->mysqlConnectDb($config['host'], $config['port'], $config['username'], $config['password'], $config['database']);
    }

    private function mysqlConnectDb($host = '127.0.0.1', $port = 3306, $username = 'root', $password = '', $database = '')
    {
		// Пытаемся подключиться к Mysql
	    try{

			$this->connect = mysqli_connect($host.':'.$port, $username, $password, $database);

			if (mysqli_connect_errno()) {
			    throw new RuntimeException('No Mysql connection: ' . mysqli_connect_error());
			}

		} catch (RuntimeException $ex) {

			echo $ex->getMessage();
		}

		return $this->connect;
    }

    public function mysqlClose()
    {
		mysqli_close($this->connect);
    }

	public function select($table)
	{	
		$sql = 'SELECT `code`, `name`, `level1`, `level2`, `level3`, `price`, `price_jp`, `count`, `properties`, `joint_purchases`, `unit`, `picture`, `on_index`, `description` FROM ' . $table . ';';

	    try{

			$result = mysqli_query($this->connect, $sql);

			if (!$result) {
				throw new RuntimeException('Could not successfully run query ($sql) from DB: ' . mysql_error());
			}

		} catch (RuntimeException $ex) {

			echo $ex->getMessage();
		}


		$rows = [];
		while ($row = mysqli_fetch_assoc($result)) {
			$rows[] = $row;
		}

		mysqli_free_result($result);

		return $rows;
	}	

	public function merge($table1, $table2)
	{	
		$sql = 'INSERT INTO '. $table1 .'  (code, name, level1, level2, level3, price, price_jp, count, properties, joint_purchases, unit, picture, on_index, description)
		SELECT code, name, level1, level2, level3, price, price_jp, count, properties, joint_purchases, unit, picture, on_index, description FROM '. $table2 .' 
		ON DUPLICATE KEY UPDATE code=VALUES(code), name=VALUES(name), level1=VALUES(level1), level2=VALUES(level2), level3=VALUES(level3), price=VALUES(price), price_jp=VALUES(price_jp), count=VALUES(count), properties=VALUES(properties), joint_purchases=VALUES(joint_purchases), unit=VALUES(unit), picture=VALUES(picture), on_index=VALUES(on_index), description=VALUES(description);';

	    try{

			$result = mysqli_query($this->connect, $sql);

			if (!$result) {
				throw new RuntimeException('Could not successfully run query ($sql) from DB: ' . mysql_error());
			}

		} catch (RuntimeException $ex) {

			echo $ex->getMessage();
		}

	}	

	public function insert($table, $data)
	{
		$sql = 'INSERT INTO ' . $table . ' (`code`, `name`, `level1`, `level2`, `level3`, `price`, `price_jp`, `count`, `properties`, `joint_purchases`, `unit`, `picture`, `on_index`, `description`) VALUES(\''.$data->code.'\', \''.$data->name.'\', \''.$data->level1.'\', \''.$data->level2.'\', \''.$data->level3.'\', '.$data->price.', '.$data->price_jp.', '.$data->count.', \''.$data->properties.'\', '.$data->joint_purchases.', \''.$data->unit.'\', \''.$data->picture.'\', '.$data->on_index.', \''.$data->description.'\');';

		try{

			$result = mysqli_query($this->connect, $sql);

			if (!$result) {
				throw new RuntimeException('Could not successfully run query ($sql) from DB: ' . mysql_error());
			}

		} catch (RuntimeException $ex) {

			echo $ex->getMessage();
		}

	}

	public function cleanPriceTmp()
	{
		$sql = "DELETE FROM price_tmp;";
		mysqli_query($this->connect, $sql);
	}

	public function createTables()
	{
		$sql = "CREATE TABLE IF NOT EXISTS price (
			code varchar(30) NOT NULL,
			name varchar(255) NULL,
			level1 varchar(100) NULL,
			level2 varchar(100) NULL,
			level3 varchar(100) NULL,
			price DECIMAL NULL,
			price_jp DECIMAL NULL,
			count DECIMAL NULL,
			properties varchar(255) NULL,
			joint_purchases BOOL NULL,
			unit varchar(10) NULL,
			picture varchar(150) NULL,
			on_index BOOL NULL,
			description varchar(500) NULL
		)
		ENGINE=InnoDB
		DEFAULT CHARSET=utf8mb4
		COLLATE=utf8mb4_general_ci;";
		mysqli_query($this->connect, $sql);

		$sql = "CREATE UNIQUE INDEX IF NOT EXISTS price_code_IDX ON price (code);";
		mysqli_query($this->connect, $sql);

		$sql = "CREATE TABLE IF NOT EXISTS price_tmp (
			code varchar(30) NOT NULL,
			name varchar(255) NULL,
			level1 varchar(100) NULL,
			level2 varchar(100) NULL,
			level3 varchar(100) NULL,
			price DECIMAL NULL,
			price_jp DECIMAL NULL,
			count DECIMAL NULL,
			properties varchar(255) NULL,
			joint_purchases BOOL NULL,
			unit varchar(10) NULL,
			picture varchar(150) NULL,
			on_index BOOL NULL,
			description varchar(500) NULL
		)
		ENGINE=InnoDB
		DEFAULT CHARSET=utf8mb4
		COLLATE=utf8mb4_general_ci;";
		mysqli_query($this->connect, $sql);

		$sql = "CREATE UNIQUE INDEX IF NOT EXISTS price_tmp_code_IDX ON price_tmp (code);";
		mysqli_query($this->connect, $sql);
	}
}
