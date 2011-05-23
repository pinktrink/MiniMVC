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
abstract class mmvc_model extends mmvc_base{
	/**
	 * @var resource $db
	 * @access public
	 */
	public $db;
	
	/**
	 * @var bool $isdb
	 * @access private
	 */
	private $has_db = false;
	
	/**
	 * mmvc_model::__construct
	 *
	 * This function handles the contruction of a model, all that it really does is include the db if necessary
	 * 
	 * @author Eric Kever <ekever@reusserdesign.com>
	 * @param bool $include_db Whether to include the database with the model or not
	 * @return void
	 */
	public function __construct($include_db = true, $driver_options = array()){
		if(!(boolean)$include_db) return;
		include MMVC_CORE_DIRECTORY . '/config.php';
		$this->db = new PDO(
			MMVC_MODEL_DB_TYPE . ':host=' . MMVC_MODEL_DB_SERVER . ';dbname=' . MMVC_MODEL_DB_DATABASE,
			MMVC_MODEL_DB_USERNAME,
			MMVC_MODEL_DB_PASSWORD,
			$driver_options
		);
		$this->has_db = true;
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
		if($this->has_db) $this->db->close();
	}
}
?>