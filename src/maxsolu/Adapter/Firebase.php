<?php 

namespace Ack\Foundation\Adapter;

use \Google\Cloud\Firestore\FirestoreClient;


class Firebase {
	
	protected static $db;
	public $conn;
	private $projectId;
	
	public static function db($projectId='notification-935ad') {
			if (is_null ( self::$db )) {
			self::$db = new self ();
			self::$db->projectId = $projectId;
			if (is_null ( self::$db->projectId ) || strlen ( self::$db->projectId ) < 2) {
				return null;
			}
			
			
			self::$db->conn = new FirestoreClient([
				'projectId' =>$projectId,
				//'keyFilePath' =>  '/path/to/keyfile.json',
			]);
		}
		if (strlen ( $projectId ) > 0 && self::$db->projectId != $projectId) {
			self::$db = new self ();
			self::$db->projectId = $projectId;
			if (is_null ( self::$db->projectId ) || strlen ( self::$db->projectId ) < 2) {
				return null;
			}
			self::$db->conn = new FirestoreClient([
				'projectId' =>$projectId,
				//'keyFilePath' =>  '/path/to/keyfile.json',
			]);
		}
		
		return self::$db;

	}
	public function addData($collection,$data) {
		$addedDocRef = self::$db->conn->collection($collection)->add($data);
		return  $addedDocRef->id();
	}
	public function updateData($collection,$document,$data) {
		
		$database =  self::$db->conn->collection($collection)->document($document);
		$updateData=array();
		if(count($data)>0){
			foreach($data as $k=>$v){
				$updateData[]=array('path'=>$k,'value'=>$v);
			}
			$database->update($updateData);
			return true;
		}
		return false;
		
	}
	public function setData($collection,$document,$data) {
	
		try{
			$cityRef = self::$db->conn->collection($collection)->document($document);
			$cityRef->set($data);
			return true;
		}catch(\exeption $e){
			return false;
		}
		return false;
		
	}
	public function deleteData($collection,$document) {
		$db->collection('samples/php/cities')->document('DC')->delete();
		return true;
	}
	public function deleteDataField($collection,$document,$field) {
		$cityRef = $db->collection('samples/php/cities')->document('BJ');
		$cityRef->update([
			['path' => 'capital', 'value' => FieldValue::deleteField()]
		]);
		return true;
	}
	public function getDataByDocument($collection,$document) {
		$docRef = $db->collection('samples/php/cities')->document('SF');
		$snapshot = $docRef->snapshot();
		$data=[];
		if ($snapshot->exists()) {
			$data = $snapshot->data();
		} else {
			return false;
		}
		return $data;
	}
	public function getData($collection,$where=[]){

   // < 小于 <= 小于或等于 == 等于 > 大于>= 大于或等于 != 不等于 array-contains   array-contains-any   in    not-in     format  [array('order_id','>',11)]
		$data=[];
		$query = self::$db->conn->collection($collection);
		
		$query = $query->limit(25);
		//$citiesRef->orderBy('population');
		//$citiesRef-->orderBy('name');
		//$citiesRef-->orderBy('state');
		//$citiesRef-->startAt(['Springfield']);
		//$citiesRef->startAt($snapshot);
		
		if(count($where)>0){
			
			foreach($where as $w){
				$query = $query->where($w[0],$w[1],$w[2]);
			}
			
			$documents = $query->documents();
		}else{
			$documents = $query->documents();
		}	
		foreach ($documents as $document) {
			if ($document->exists()) {
				//printf('Document data for document %s:' . PHP_EOL, $document->id());
				$data[$document->id()]=$document->data();
				
			} 
		}
		return  $data;
	}
	public function getDocByDocument(){
		$cityRef = $db->collection('samples/php/cities')->document('SF');
		$collections = $cityRef->collections();
		foreach ($collections as $collection) {
			printf('Found subcollection with id: %s' . PHP_EOL, $collection->id());
		}
	}
}