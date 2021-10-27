<?php 
namespace Ack\Foundation\Adapter;

class Adapter {
	protected static $instance;
	public $conn;
	private $token;
	public static function getInstance($token = 'token') {
		if (is_null ( self::$instance )) {
			self::$instance = new self ();
			self::$instance->token = $token;
			if (is_null ( self::$instance->token ) || strlen ( self::$instance->token ) < 2) {
				return null;
			}
			$config = (new \Laminas\Config\Reader\Ini ())->fromFile ( ROOT . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . self::$instance->token . DIRECTORY_SEPARATOR . 'data.ini' );
			
			self::$instance->conn = new \Laminas\Db\Adapter\Adapter ( array (
					'driver' => $config ['database'] ['driver'],
					'dsn' => $config ['database'] ['dsn'],
					'username' => $config ['database'] ['username'],
					'password' => $config ['database'] ['password'],
					'driver_options' => array(/*PDO::MYSQL_ATTR_INIT_COMMAND*/1002 => 'SET NAMES \'UTF8\'' 
					) 
			) );
		}
		if (strlen ( $token ) > 0 && self::$instance->token != $token) {
			self::$instance = new self ();
			self::$instance->token = $token;
			if (is_null ( self::$instance->token ) || strlen ( self::$instance->token ) < 2) {
				return null;
			}
			$config = (new \Laminas\Config\Reader\Ini ())->fromFile ( ROOT . DIRECTORY_SEPARATOR . 'local' . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . self::$instance->token . DIRECTORY_SEPARATOR . 'data.ini' );
			self::$instance->conn = new \Laminas\Db\Adapter\Adapter ( array (
					'driver' => $config ['database'] ['driver'],
					'dsn' => $config ['database'] ['dsn'],
					'username' => $config ['database'] ['username'],
					'password' => $config ['database'] ['password'],
					'driver_options' => array(/*PDO::MYSQL_ATTR_INIT_COMMAND*/1002 => 'SET NAMES \'UTF8\'' 
					) 
			) );
		}
		return self::$instance;
	}
	public function getModel($entity, $key = 'entity_id', $lang='',$type_key='') {
		return new Model ( $entity, $key,$lang,$type_key );
	}
	public function execQuery($sql = '') {
		return $this->conn->driver->getConnection ()->execute ( $sql );
	}
	public function runsql($sql = '') {
		$stmt = $this->conn->driver->getConnection ()->execute ( $sql );
		$resultSet = new \Laminas\Db\ResultSet\ResultSet ();
		$resultSet->initialize ( $stmt );
		return $resultSet->toArray ();
	}
}