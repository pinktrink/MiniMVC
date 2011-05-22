<?php
/**
 * class mmvc_model extends mmvc_base
 *
 * MiniMVC model class, extended by user
 *
 * @copyright Copyright (c) 2010, Eric Kever
 * @author Eric Kever <ekever@reusserdesign.com>
 * @abstract
 */
abstract class mmvc_model extends mmvc_base
{
	/**
	 * @var resource $db
	 * @access public
	 */
	public $db;
	
	/**
	 * @var bool $isdb
	 * @access private
	 */
	private $isdb = false;
	
	/**
	 * mmvc_model::__construct
	 *
	 * This function handles the contruction of a model, all that it really does is include the db if necessary
	 * 
	 * @author Eric Kever <ekever@reusserdesign.com>
	 * @param bool $include_db Whether to include the database with the model or not
	 * @return void
	 */
	public function __construct($include_db = true){
		if(!(boolean)$include_db) return;
		include MMVC_CORE_DIRECTORY . '/config.php';
		$this->db = new mysqli(MMVC_MODEL_DB_SERVER, MMVC_MODEL_DB_USERNM, MMVC_MODEL_DB_PASSWD, MMVC_MODEL_DB_DATABS, MMVC_MODEL_DB_PORTNM, MMVC_MODEL_DB_SOCKET);
		$this->isdb = true;
	}
	
	/**
	 * mmvc_model::__destruct
	 *
	 * This function handles the destruction of a model, closes the db if it is included
	 *
	 * @author Eric Kever <ekever@reusserdesign.com>
	 * @return void
	 */
	public function __destruct(){
		if($this->isdb) $this->db->close();
	}
}
?>