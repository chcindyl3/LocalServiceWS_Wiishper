<?php
	
	require_once 'login_mysql.php';
	
	class DBConnection
	{
		private static $db = null;
		
		private static $pdo;
		
		final private function __construct()
		{
			try
			{
				self::obtainDB();
			} 
			catch (PDOException $e)
			{
				
			}
		}
		
		public static function getInstance()
		{
			if (self::$db === null)
			{
				self::$db = new self();
			}
			return self::$db;
		}
		
		public function obtainDB()
		{
			if(self::$pdo == null)
			{
				self::$pdo = new PDO(
					'mysql:dbname=' . DATABASE . ';host=' . HOSTNAME . ";" . "unix_socket=/var/run/mysql/1.sock;",
					USERNAME,
					PASSWORD,
					array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")
				);
				
				self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			}
			
			return self::$pdo;
		}
		
		final protected function __clone()
		{
		}
		
		function _destructor()
		{
			self::$pdo = null;
		}
	}