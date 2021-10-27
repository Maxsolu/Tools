<?php

namespace Ack\Foundation\Adapter;

use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\Where;
use Laminas\Db\RowGateway\AbstractRowGateway;
use Laminas\Db\TableGateway\TableGateway;
use Aemik\Foundation\Cache\Cache;
use Laminas\Db\TableGateway\Feature\RowGatewayFeature;
use Laminas\Db\Sql\Select;
use Aemik\Foundation\Tools\Tools;
use Laminas\Db\Sql\Ddl\Column;

class Model extends AbstractRowGateway {
	protected $key = 'entity_id';
	protected $type_key = '';
	protected $tableGateway;
	protected $primaryKeyColumn = array ();
	protected $table = '';
	public function __construct($table, $key ='entity_id', $lang='',$type_key='') {
	
		$this->table = $table;
		$this->key=$key;
		$this->type_key=$type_key;
		$this->primaryKeyColumn = array (
				$this->key
		);
		$this->tableGateway = new TableGateway ( $this->table, Adapter::getInstance ()->conn,new RowGatewayFeature($this->key) );
		$this->sql = new Sql ( Adapter::getInstance ()->conn, $this->table );
		$this->initialize ();
	}

	public function getTable(){
		return $this->tableGateway;
	}
	public function countData($whereData = NULL) {
		$sql = new Sql(Adapter::getInstance ()->conn);
		$select = $sql->select();
		$select->from($this->table );
		if($whereData)
		{
			$select->where($whereData);
		}
		$statement = $sql->prepareStatementForSqlObject($select);
		$results = $statement->execute();
		$resultSet = new \Laminas\Db\ResultSet\ResultSet ();
		$resultSet->initialize($results);
		$resultSet->buffer();
		return $resultSet->count();
	}
	public function rowCount($whereData = NULL) {
		$sql = new Sql(Adapter::getInstance ()->conn);
		$select = $sql->select();
		$select->from($this->table );
		$select->columns(array('count'=>new \Laminas\Db\Sql\Expression('COUNT(*)')));
		if($whereData)
		{
			$select->where($whereData);
		}
		$statement = $sql->prepareStatementForSqlObject($select);
		$results = $statement->execute();
		return $results->current();
	
	}

	public function getMeta() {
		return new \Laminas\Db\Metadata\Metadata ( Adapter::getInstance ()->conn );
	}
	public function getFields() {
		return $this->getMeta ()->getColumnNames ( $this->table );
	}
	public function getColumns() {
		$data=array();
		$Columns =  $this->getMeta ()->getColumns ( $this->table );
	
		foreach($Columns as $c){
			switch ($c->getDataType()){
			case 'int' :
				$data[$c->getName()]=(int)0;
				break;
			default:
				$data[$c->getName()]=(string)'';	
			}
		}
	return $data;
		
	}
	public function select($params = array()) {
		return $this->tableGateway->select ( $params );
	}
	public function getEntity($params = array()) {

		$data =Adapter::getInstance ()->getModel($this->table,'entity_id')->select($params)->current();
		
	
		return $data;
	}
	public function order($params = array()) {
		return array();
			
	}
	public function where($params = array(),$order=false,$limit=false,$offset=false,$columns=false,$greater= array(),$less= array(),$like= array()) {
	
	    $data = $this->tableGateway->select ( function (Select $select)use($params,$order,$limit,$offset,$columns,$greater,$less,$like) {
		if(count($params)>0||count($greater)>0||count($less)>0||count($like)>0){
				$where = new Where ();
				if(count($params)>0){
				foreach($params as $k=>$v){
					$where->equalTo ( $k, $v);
				}}
				if(count($greater)>0){
				foreach ($greater as $biger=>$smaller){
				    $where->greaterThanOrEqualTo($biger,$smaller);
				}
				}
				if(count($less)>0){
				    foreach ($less as $smaller=>$biger){
				        $where->lessThanOrEqualTo($smaller,$biger);
				    }
				}
				if(count($like)>0){
				    foreach ($like as $item=>$itemlike){
				        $where->like($item,$itemlike);
				    }
				}
				$select->where ( $where );
				
			};
				if($order){
					$select->order($order);
				}
				if($limit){
					$select->limit($limit);
				}
				if($offset){
					$select->offset($offset);
				}
				if($columns){
					$select->columns ( $columns );
				}
			} 
											 
		);
		
			return $data;	
	
	}
	
	
	public function db($table = '', $params = array()) {
		return (new TableGateway ( $table, Adapter::getInstance ()->conn, new \Laminas\Db\TableGateway\Feature\RowGatewayFeature ( $this->key ) ))->select ( $params );
	}
	public function deleteData($where=array()){
		$a = $this->select($where);
		if(count($a)>0){
			$lang=Adapter::getInstance ()->getModel('core_lang','entity_id')->select(array('name'=>$this->table))->current();
			if ($lang) {
				$db_scheme = $lang->db_scheme;
				foreach($a as $aa){
					Adapter::getInstance ()->getModel($db_scheme,'entity_id')->deleteData(array('oid'=>$aa->entity_id));
				}
			}
			$delete  = new TableGateway ( $this->table, Adapter::getInstance ()->conn, new \Laminas\Db\TableGateway\Feature\RowGatewayFeature ( $this->key ) );
			$delete->delete($where);
		}
		return true;
	}
	public function update($array=array(), $where=array()){
		foreach($array as $k=>$v){
			$set[htmlentities($k)]=htmlentities($v);
		}
	    return (new TableGateway ( $this->table, Adapter::getInstance ()->conn, new \Laminas\Db\TableGateway\Feature\RowGatewayFeature ( $this->key ) ))->update($set,$where);
	}
	
	public function check($id) {
		$rowset = $this->tableGateway->select ( array (
				$this->key => ( int ) $id
		) );
		if ($rowset->current ()) {
			return true;
		}
		return false;
	}
	public function checkBy($key, $val) {
		$rowset = $this->tableGateway->select ( array (
				$key => $val
		) );
		if ($rowset->current ()) {
			return true;
		}
		return false;
	}
	public function checkBys($params=array()) {
		$rowset = $this->tableGateway->select ($params);
		if ($rowset->current ()) {
			return true;
		}
		return false;
	}
	public function setArrayData($array = array()) {
		foreach($array as $k=>$v){
			$input[htmlentities($k)]=htmlentities($v);
		}
		$data = array ();
		foreach ( $this->getFields () as $field ) {
			if (isset ( $input [$field] )) {
				$data [$field] = $input [$field];
			}
		}
		return $this->keepInput ( $data );
	}

	public function setEntity($array = array()) {
		foreach($array as $k=>$v){
			$input[htmlentities($k)]=htmlentities($v);
		}
	
		$obj = $this;
		foreach ( $this->getFields () as $field ) {
			if (isset ( $input [$field] )) {
				$obj->{$field} = $input [$field];
			}
		}
		$obj->keep ();
			
		if (( int ) $obj->entity_id > 0) {
			$lang=Adapter::getInstance ()->getModel('core_lang','entity_id')->select(array('name'=>$this->table))->current();
			if ($lang) {
				$newarray=$array;
				$newarray['entity_id']=0;
				$db_scheme = $lang->db_scheme;
				$newarray['oid'] = ( int ) $obj->entity_id;
				$newarray['lan'] = $this->lang;
				$check = Adapter::getInstance ()->getModel($db_scheme,'entity_id')->select(array('oid'=>$obj->entity_id,'lan'=>$this->lang))->current();
			
				if($check){
					$newarray['entity_id'] =  $check->entity_id;
				}
				Adapter::getInstance ()->getModel($db_scheme,'entity_id')->setEntity($newarray);
				
			}
			
			$core=Adapter::getInstance ()->getModel('core_type','type_id')->select(array('name'=>$this->type_key))->toArray();
			if ($core && is_array ( $core ) && count ( $core ) > 0) {
				foreach ( $core as $c ) {
					if (isset ( $input [$c ['varkey']] )) {
						$c ['entity_id'] = $obj->entity_id;
						$c ['value'] = $input [$c ['varkey']];
						$this->setDataByType ( $c );
					}
				}
			}
			return ( int ) $obj->entity_id;
		}
		return 0;
	}
	public function deleteEntity($entity_id=0){
		
		try{
			$obj = $this->select(array($this->key=>$entity_id))->current();
			
			if($obj){
				$core=Adapter::getInstance ()->getModel('core_type','type_id')->select(array('name'=>$this->type_key))->toArray();
				
				if ($core && is_array ( $core ) && count ( $core ) > 0) {
					foreach ( $core as $c ) {
						$table = $this->table . '_' . $c['db_scheme'];
						$tableGateway = new TableGateway ( $this->table, Adapter::getInstance ()->conn,new RowGatewayFeature('value_id') );
						$tableGateway->delete(array('entity_id'=>(int)$obj->entity_id, (int)$c['type_id']));
					}
				}
				
				$obj->delete();
				return true;
			}
			
		}
		catch(Exception $e){
			return false;
		}
	}

	public function setObjData($array = array()) {
		foreach($array as $k=>$v){
			$input[htmlentities($k)]=htmlentities($v);
		}
		$data = array ();
		foreach ( $this->getFields () as $field ) {
			if (isset ( $input->{$field} )) {
				$data [$field] = $input->{$field};
			}
		}
		$this->keepInput ( $data );
	}
	private function keepInput($data = array()) {
		if (! isset ( $data [$this->key] ) || ( int ) $data [$this->key] == 0) {
		    Tools::log($this->key);
			$this->tableGateway->insert ( $data );
			return $this->tableGateway->lastInsertValue;
		} else {
			if ($this->check ( ( int ) $data [$this->key] )) {
				$this->tableGateway->update ( $data, array (
						$this->key => ( int ) $data [$this->key]
				) );
			}
			return ( int ) $data [$this->key];
		}
	}
	public function keep() {
		$model = $this;
		if (is_array ( $this->data ) && count ( $this->data ) > 0) {
			foreach ( $this->data as $k => $v ) {
				$this->data [$k] = $this->filterData ( $this->getMeta ()->getColumn ( $k, $this->table )->getDataType (), $v );
			}
		}
		if (! isset ( $model->{$this->key} ) || ( int ) $model->{$this->key} == 0) {
			$model->save ();
		} else {
			if ($this->check ( ( int ) $model->{$this->key} )) {
				$this->tableGateway->update ( $this->data, array (
						$this->key => ( int ) $model->{$this->key}
				) );
			}else{
				$model->save ();
			}
		}
	}
	public function setDataByType($array = array(), $key = 'value_id') {
		foreach($array as $k=>$v){
			$input[htmlentities($k)]=htmlentities($v);
		}
		$table = $this->table . '_' . $input ['db_scheme'];
		$data = array ();
		$fields = $this->getMeta ()->getColumnNames ( $table );
		foreach ( $fields as $field ) {
			if (isset ( $input [$field] )) {
				$data [$field] = $input [$field];
			}
		}
		$this->_setUData ( $table, $data, $key, 'entity_id', 'type_id' );
	}
	private function _setUData($table, $data, $key, $key2 = '', $key3 = '') {
		$tableGateway = new TableGateway ( $table, Adapter::getInstance ()->conn );
		if (isset ( $data [$key] ) && ( int ) $data [$key] > 0) {
			if ($this->check ( ( int ) $data [$key] )) {
				$tableGateway->update ( $data, array (
						$key => ( int ) $data [$key]
				) );
				return;
			}
		}

		if (isset ( $data [$key2] ) && ( int ) $data [$key2] > 0 && isset ( $data [$key3] ) && ( int ) $data [$key3] > 0) {
			$rowset = $tableGateway->select ( array (
					$key2 => ( int ) $data [$key2],
					$key3 => ( int ) $data [$key3]
			) )->current ();
			if ($rowset) {
				$tableGateway->update ( $data, array (
						$key2 => ( int ) $data [$key2],
						$key3 => ( int ) $data [$key3]
				) );
				return;
			}
		}

		$tableGateway->insert ( $data );
	}
	public function getEntityValueBy($var = '', $type_id = 0, $entity_id = 0) {
		return Adapter::getInstance ()->getModel($this->table . '_' . $var, 'value_id')->select ( array (
				'type_id' => ( int ) $type_id,
				'entity_id' => ( int ) $entity_id
		) )->current ();
	}
	public function getObjByEntityId($entity_id = 0, $store_id=0, $attribute_set_id=0) {
		$obj = $this->tableGateway->select ( array (
				'entity_id' => ( int ) $entity_id,
				'store_id' => (int)$store_id,
				'attribute_set_id' => (int)$attribute_set_id
		) )->toArray ();
		if (count ( $obj ) > 0) {
			$data = $obj [0];
			$core=Adapter::getInstance ()->getModel('core_type','type_id')->select(array('name'=>$this->type_key))->toArray();
			if ($core && is_array ( $core ) && count ( $core ) > 0) {
				foreach ( $core as $c ) {
					$v = ($this->getEntityValueBy( $c ['db_scheme'], ( int ) $c ['type_id'], ( int ) $entity_id ));
					if ($v) {
						$data [$c ['varkey']] = $v->value;
					}else{
						$data [$c ['varkey']] = null;
					}
				}
			}
			return $data;
		}
		return null;
	}
	public function getObjByArray ($data = array()) {
	    $obj = $this->tableGateway->select ( $data );
	    if (count ( $obj ) > 0) {
	        foreach ($obj as $o){
				$newdata[] = $this->getObjByEntityId( ( int ) $o->entity_id );
	        }
	        return $newdata;

	    }
	    return null;
	}
	public function filterData($t, $data) {
		switch ($t) {
			case 'char' :
				return ( string ) $data;
				break;
			case 'varchar' :
				return ( string ) $data;
				break;
			case 'tinytext' :
				return ( string ) $data;
				break;
			case 'text' :
				return ( string ) $data;
				break;
			case 'mediumtext' :
				return ( string ) $data;
				break;
			case 'longtext' :
				return ( string ) $data;
				break;
			case 'binary' :
				return ( string ) $data;
				break;
			case 'varbinary' :
				return ( string ) $data;
				break;
			case 'bit' :
				return ( string ) $data;
				break;
			case 'tinyint' :
				return ( int ) $data;
				break;
			case 'smallint' :
				return ( int ) $data;
				break;
			case 'mediumint' :
				return ( int ) $data;
				break;
			case 'int' :
				return ( int ) $data;
				break;
			case 'integer' :
				return ( int ) $data;
				break;
			case 'bigint' :
				return ( int ) $data;
				break;
			case 'decimal' :
				return ( float ) $data;
				break;
			case 'dec' :
				return ( float ) $data;
				break;
			case 'date' :
				return ( string ) $data;
				break;
			case 'numeric' :
				return ( float ) $data;
				break;
			case 'fixed' :
				return ( float ) $data;
				break;
			case 'float' :
				return ( float ) $data;
				break;
			case 'double' :
				return ( float ) $data;
				break;
			case 'double precision' :
				return ( float ) $data;
				break;
			case 'real' :
				return ( float ) $data;
				break;
			case 'bool' :
				return ( int ) $data;
				break;
			case 'boolean' :
				return ( int ) $data;
				break;
			case 'tinyblob' :
				return ( int ) $data;
				break;
			case 'blob' :
				return ( string ) $data;
				break;
			case 'mediumblob' :
				return ( string ) $data;
				break;
			case 'longtext' :
				return ( string ) $data;
				break;
			case 'timestamp' :
				return ( string ) $data;
				break;
			case 'time' :
				return ( string ) $data;
				break;
		}
	}
}
