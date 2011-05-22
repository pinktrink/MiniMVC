<?php
define('DBSESS_MYSQL_HOST', 'rdprojectreview.com');  //Server host
define('DBSESS_MYSQL_USER', 'naccap2_web');  //Server user name
define('DBSESS_MYSQL_PASS', 'BOOMER');  //Server password
define('DBSESS_MYSQL_DB', 'naccap2_web_mysql');  //Server database
define('DBSESS_MYSQL_MEM_TBL', 'sess_mem');  //Table for memory session storage
define('DBSESS_MYSQL_SWAP_TBL', 'sess_swap');  //Table for swap session storage
define('DBSESS_MYSQL_DATA_MAX', 8192);  //Max session size before sending to swap, 'auto' for automatic
define('DBSESS_MYSQL_SESS_TIMEOUT', 60 * 15);  //Session timeout for garbage collection
define('DBSESS_MYSQL_ENCRYPTED', false);  //Encrypted session data?
define('DBSESS_MYSQL_HASH_FUNCTION', 'sha256');  //Hashing function for ID

//DO NOT EDIT ANYTHING BELOW THIS LINE

if(!isset($DBSESS_MYSQL_NON_VOLATILE)){
	class dbsess{
		private $db;
		private $type;
		private $table;
		private $max;
		private $GET_DATA;
		private $SET_DATA;
		private $INSERT;
		private $EXISTS;
		private $DELETE;
		private $GARBAGE_COLLECT;
		private $UPDATE;
		private $WHERE;
		private $GETMAX;
		
		public function __construct(){
			$this->GET_DATA = 'SELECT data FROM ' . DBSESS_MYSQL_DB . '.%s WHERE id = \'%s\';';
			$this->SET_DATA = 'UPDATE ' . DBSESS_MYSQL_DB . '.%s SET data = \'%s\' WHERE id = \'%s\';';
			$this->DELETE = 'DELETE FROM ' . DBSESS_MYSQL_DB . '.%s WHERE id = \'%s\'';
			$this->GARBAGE_COLLECT = 'DELETE FROM '. DBSESS_MYSQL_DB . '.' . DBSESS_MYSQL_SWAP_TBL . ', ' . DBSESS_MYSQL_DB . '.' . DBSESS_MYSQL_SWAP_TBL . ' WHERE time < \'%s\';';
			$this->UPDATE = 'UPDATE ' . DBSESS_MYSQL_DB . '.%s SET time = CURRENT_TIMESTAMP WHERE id = \'%s\';';
			$this->INSERT = 'INSERT INTO ' . DBSESS_MYSQL_DB . '.%s (id, data) VALUES (\'%s\', \'%s\');';
			$this->EXISTS = 'SELECT id FROM ' . DBSESS_MYSQL_DB . '.%s WHERE id = \'%s\';';
			$this->WHERE = 'SELECT (SELECT id FROM ' . DBSESS_MYSQL_DB . '.' . DBSESS_MYSQL_MEM_TBL . ' WHERE id = \'%s\') AS memid, (SELECT id FROM ' . DBSESS_MYSQL_DB . '.' . DBSESS_MYSQL_SWAP_TBL . ' WHERE id = \'%1$s\') AS swapid;';
			$this->GETMAX = 'SELECT CHARACTER_MAXIMUM FROM information_schema.columns WHERE column_name = \'data\' AND table_name = \'' . DBSESS_MYSQL_MEM_TBL . '\' and TABLE_SCHEMA = \'' . DBSESS_MYSQL_DB . '\';';
			ini_set('session.save_handler', 'user');
			if(!$this->connect()) return false;
			if(DBSESS_MYSQL_DATA_MAX === 'auto'){
				$lengtharr = $this->db->query(sprintf($this->GETMAX))->fetch_assoc();
				$this->max = $lengtharr['CHARACTER_MAXIMUM'];
			}else $this->max = DBSESS_MYSQL_DATA_MAX;
			$this->table = $this->db->real_escape_string(DBSESS_MYSQL_MEM_TBL);
		}
		
		private function connect(){
			if(!($this->db = mysqli_connect(
				DBSESS_MYSQL_HOST,
				DBSESS_MYSQL_USER,
				DBSESS_MYSQL_PASS,
				DBSESS_MYSQL_DB
			))) return false;
			return true;
		}
		
		private function hashid($id){
			return hash(DBSESS_MYSQL_HASH_FUNCTION, $id);
		}
		
		private function update_timestamp($id){
			$id = $this->db->real_escape_string($this->hashid($id));
			$this->db->query(sprintf($this->UPDATE, $this->table, $id));
		}
		
		private function exists($id){
			return (boolean)$this->db->query(sprintf($this->EXISTS, $this->table, $id))->num_rows;
		}
		
		private function where_is($id){
			$rawid = $this->hashid($id);
			$id = $this->db->real_escape_string($rawid);
			$resarr = $this->db->query(sprintf($this->WHERE, $id))->fetch_assoc();
			return ($resarr['memid'] === $rawid ? 'mem' : ($resarr['swapid'] === $rawid ? 'swap' : false));
		}
		
		private function db(){
			if(!$this->db->ping()) if(!$this->connect()) return false;
			$ref = &$this->db;
			return $ref;
		}
		
		private function switch_table($id){
			if($this->table === DBSESS_MYSQL_MEM_TBL){
				$this->destroy($id);
				$this->table = $this->db->real_escape_string(DBSESS_MYSQL_SWAP_TBL);
			}else if($this->table === DBSESS_MYSQL_SWAP_TBL){
				$this->destroy($id);
				$this->table = $this->db->real_escape_string(DBSESS_MYSQL_MEM_TBL);
			}
			return true;
		}
		
		private function switch_if_necessary($id, $data){
			if($this->table === DBSESS_MYSQL_MEM_TBL && strlen((binary)$data) > DBSESS_MYSQL_DATA_MAX) $this->switch_table($id);
			else if($this->table === DBSESS_MYSQL_SWAP_TBL && strlen((binary)$data) < DBSESS_MYSQL_DATA_MAX) $this->switch_table($id);
			return true;
		}
		
		private function switch_accordingly($id){
			if(!(boolean)($location = $this->where_is($id))) return false;
			switch($location){
				case 'mem':
					$this->table = $this->db->real_escape_string(DBSESS_MYSQL_MEM_TBL);
					break;
				case 'swap':
					$this->table = $this->db->real_escape_string(DBSESS_MYSQL_SWAP_TBL);
			}
			return true;
		}
		
		public function open(){
			if(!isset($this->db)){
				if(!$this->connect()) return false;
			}else if(!$this->db->ping()) if(!$this->connect()) return false;
			return true;
		}
		
		public function close(){
			if($this->db) $this->db->close();
			return true;
		}
		
		public function read($id){
			$this->switch_accordingly($id);
			$id = $this->db->real_escape_string($this->hashid($id));
			$arr = $this->db->query(sprintf($this->GET_DATA, $this->table, $id))->fetch_assoc();
			$data = $arr['data'];
			return (string)$data;
		}
		
		public function write($id, $data){
			$this->switch_accordingly($id);
			$data = $this->db->real_escape_string($data);
			$this->switch_if_necessary($id, $data);
			$id = $this->db->real_escape_string($this->hashid($id));
			if($this->exists($id)) $this->db->query(sprintf($this->SET_DATA, $this->table, $data, $id));
			else $this->db->query(sprintf($this->INSERT, $this->table, $id, $data));
			return true;
		}
		
		public function destroy($id){
			$this->switch_accordingly($id);
			$id = $this->db->real_escape_string($this->hashid($id));
			$this->db->query(sprintf($this->DELETE, $this->table, $id));
			return true;
		}
		
		public function garbage_collect(){
			$time = $this->db->real_escape_string(time() - DBSESS_MYSQL_SESS_TIMEOUT);
			$this->db->query(sprintf($this->GARBAGE_COLLECT, $time));
			return true;
		}
		
		public function __destruct(){
			session_write_close();
		}
	}
}else{
	class dbsess{
		private $db;
		private $GET_DATA;
		private $SET_DATA;
		private $INSERT;
		private $EXISTS;
		private $DELETE;
		private $GARBAGE_COLLECT;
		private $UPDATE;
		
		public function __construct(){
			$tbl = DBSESS_MYSQL_DB . '.' . DBSESS_MYSQL_SWAP_TBL;
			$this->GET_DATA = 'SELECT data FROM ' . $tbl . ' WHERE id = \'%s\';';
			$this->SET_DATA = 'UPDATE ' . $tbl . ' SET data = \'%s\' WHERE id = \'%s\';';
			$this->DELETE = 'DELETE FROM ' . $tbl . ' WHERE id = \'%s\'';
			$this->GARBAGE_COLLECT = 'DELETE FROM ' . $tbl . ' WHERE time < \'%s\';';
			$this->UPDATE = 'UPDATE ' . $tbl . ' SET time = CURRENT_TIMESTAMP WHERE id = \'%s\';';
			$this->INSERT = 'INSERT INTO ' . $tbl . ' (id, data) VALUES (\'%s\', \'%s\');';
			$this->EXISTS = 'SELECT id FROM ' . $tbl . ' WHERE id = \'%s\';';
			ini_set('session.save_handler', 'user');
			if(!$this->connect()) return false;
		}
		
		private function connect(){
			if(!($this->db = mysqli_connect(
				DBSESS_MYSQL_HOST,
				DBSESS_MYSQL_USER,
				DBSESS_MYSQL_PASS,
				DBSESS_MYSQL_DB
			))) return false;
			return true;
		}
		
		private function hashid($id){
			return hash(DBSESS_MYSQL_HASH_FUNCTION, $id);
		}
		
		private function update_timestamp($id){
			$id = $this->db->real_escape_string($this->hashid($id));
			$this->db->query(sprintf($this->UPDATE, $id));
		}
		
		private function exists($id){
			$id = $this->db->real_escape_string($this->hashid($id));
			return (boolean)$this->db->query(sprintf($this->EXISTS, $id))->num_rows;
		}
		
		private function db(){
			if(!$this->db->ping()) if(!$this->connect()) return false;
			$ref = &$this->db;
			return $ref;
		}
		
		public function open(){
			if(!isset($this->db)){
				if(!$this->connect()) return false;
			}else if(!$this->db->ping()) if(!$this->connect()) return false;
			return true;
		}
		
		public function close(){
			if($this->db) $this->db->close();
			return true;
		}
		
		public function read($id){
			$id = $this->db->real_escape_string($this->hashid($id));
			$arr = $this->db->query(sprintf($this->GET_DATA, $id))->fetch_assoc();
			$data = $arr['data'];
			return (string)$data;
		}
		
		public function write($id, $data){
			$data = $this->db->real_escape_string($data);
			$id = $this->db->real_escape_string($this->hashid($id));
			if($this->exists($id)) $this->db->query(sprintf($this->SET_DATA, $data, $id));
			else $this->db->query(sprintf($this->INSERT, $id, $data));
			return true;
		}
		
		public function destroy($id){
			$id = $this->db->real_escape_string($this->hashid($id));
			$this->db->query(sprintf($this->DELETE, $id));
			return true;
		}
		
		public function garbage_collect(){
			$time = $this->db->reql_escape_string(time() - DBSESS_MYSQL_SESS_TIMEOUT);
			$this->db->query(sprintf($this->GARBAGE_COLLECT, $time));
			return true;
		}
		
		public function __destruct(){
			session_write_close();
		}
	}
}

if(DBSESS_MYSQL_ENCRYPTED){
	class dbsess_encrypted extends dbsess{
		public function __construct(){
			parent::__construct();
		}
		
		private function encrypt($data){
			return str_rot13($data);
		}
		
		private function decrypt($data){
			return str_rot13($data);
		}
		
		public function write($id, $data){
			return parent::write($id, $this->encrypt($data));
		}
		
		public function read($id){
			return $this->decrypt(parent::read($id));
		}
	}
}

$session = (DBSESS_MYSQL_ENCRYPTED ? new dbsess_encrypted : new dbsess);
session_set_save_handler(
	array($session, 'open'),
	array($session, 'close'),
	array($session, 'read'),
	array($session, 'write'),
	array($session, 'destroy'),
	array($session, 'garbage_collect')
);
?>