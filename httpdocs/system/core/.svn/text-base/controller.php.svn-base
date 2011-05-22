<?php
/**
 * class mmvc_controller extends mmvc_base
 * 
 * MiniMVC Controller Class, extended by user controllers
 * 
 * @copyright Copyright (c) 2010, Eric Kever
 * @author Eric Kever <ekever@reusserdesign.com
 * @abstract
 */
abstract class mmvc_controller extends mmvc_base
{
	/**
	 * method load_model
	 *
	 * Loads a model, optionally into the current controller
	 *
	 * @author Eric Kever <ekever@reusserdesign.com
	 * @access public
	 * @param string $model The name of the model to load
	 * @param bool $include_db ?true Whether to include the db with the model
	 * @param bool $load_obj ?true Whether to load the model object into the controller
	 * @return mixed
	 */
	public function load_model($model, $include_db = true, $load_obj = true)
	{
		if(isset($this->db)) $include_db = false;
		$model = trim($model, '/');
		$model_obj = substr($model,
		 ((($s = strrpos($model, '/')) !== false ? $s : -1) + 1));
		if(is_file($mfile = (MMVC_CORE_DIRECTORY . "/../models/$model.php")))
		{
			include_once $mfile;
			if(property_exists($this, $model_obj)) return false;
			if($load_obj){
				$this->$model_obj = new $model_obj($include_db);
				return $model_obj;
			}else return new $model_obj($include_db);
		}
		return false;
	}
	
	/**
	 * method unload_model
	 *
	 * Unloads a model from the current controller, if loaded
	 *
	 * @author Eric Kever <ekever@reusserdesign.com
	 * @access public
	 * @param string $model The name of the model to unload
	 * @return bool
	 */
	public function unload_model($model){
		$model = trim($model, '/');
		$model_obj = substr($model,
		 ((($s = strrpos($model, '/')) !== false? $s : -1) + 1));
		if(isset($this->$model_obj)){
			unset($this->$model_obj);
			return true;
		}
		return false;
	}
	
	/**
	 * method load_view
	 * 
	 * Loads a view, either returning or outputting it
	 *
	 * @author Eric Kever <ekever@reusserdesign.com
	 * @access public
	 * @param string $view The name of the view to load
	 * @param array $array_vars ?array() An associative array of variables and their values to load into the view
	 * @param bool $return ?false Whether to return the view's data or output it
	 * @return mixed
	 */
	public function load_view($view, $array_vars = array(),
	 $return = false)
	{
		if(is_bool($array_vars)){
				$array_vars = array();
				$return = true;
		}
		$view = trim($view, '/');
		$view_obj = substr($view,
		 ((($s = strrpos($view, '/')) !== FALSE ? $s : -1) + 1));
		if(is_file($vfile = (MMVC_CORE_DIRECTORY . "/../views/$view.php")))
		{
			extract($array_vars);
			if((boolean)$return) ob_start();
			include_once $vfile;
			if((boolean)$return) return ob_get_clean();
			return true;
		}
		return false;
	}
}
?>