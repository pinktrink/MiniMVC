<?php
/**
 * class mmvc_base
 * 
 * MiniMVC Base Class, extended by controller and model
 * 
 * @copyright Copyright (c) 2010, Eric Kever
 * @author Eric Kever <ekever@reusserdesign.com>
 * @abstract
 */
abstract class mmvc_base{
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
	public function load_model($model, $include_db = true, $load_obj = true){
		if(isset($this->db)) $include_db = false;
		$model = trim($model, '/');
		$model_obj = substr($model,
		 ((($s = strrpos($model, '/')) !== false ? $s : -1) + 1));
		if(is_file($mfile = (MMVC_CORE_DIRECTORY . "/../models/$model.php"))){
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
	public function load_view($view, $array_vars = array(), $return = false){
		if(is_bool($array_vars)){
				$array_vars = array();
				$return = true;
		}
		$view = trim($view, '/');
		$view_obj = substr($view,
		 ((($s = strrpos($view, '/')) !== FALSE ? $s : -1) + 1));
		if(is_file($vfile = (MMVC_CORE_DIRECTORY . "/../views/$view.php"))){
			extract($array_vars);
			if((boolean)$return) ob_start();
			include_once $vfile;
			if((boolean)$return) return ob_get_clean();
			return true;
		}
		return false;
	}
	
	/**
	 * mmvc_base::load_hf_view
	 * 
	 * Loads a view, but with a header and footer attached.
	 * 
	 * @author Eric Kever <ekever@reusserdesign.com>
	 * @access public
	 * @param string $view The view to be loaded
	 * @param string $title The page title
	 * @param array|bool $vars An associative array of variable names and values to be loaded into the view.  If passed as a boolean it will set the $return parameter
	 * @param array|bool $inc An array of files with extensions js, jq, or css, that will be included in the header of the view.  If passed as a boolean it will set the $return parameter
	 * @param bool $return Whether the view's data should be returned by the method call or output
	 * @param string $hfprefix A prefix to place before the _header and _footer models, without a trailing /
	 * @return string|int String: The resulting output the views produced, Int: A bitflag representation of the boolean returns of each load_view.
	 */
	public function load_hf_view($view, $title, $vars = array(), $inc = array(), $return = false, $hfprefix = ''){
		$css = $js = $extra = '';  //Need $css, $js, and $extra for parallel inclusion.
		
		if(is_bool($vars)){
			$return = $vars;
			$vars = array();
		}
		
		if(is_bool($inc)){
			$return = $inc;
			$inc = array();
		}
		
		$headervars = array();
		$footervars = array();
		
		if(isset($vars['.header'])){
			$headervars = $vars['.header'];
			unset($vars['.header']);
		}
		if(isset($vars['.footer'])){
			$footervars = $vars['.footer'];
			unset($vars['.footer']);
		}
		
		foreach($inc as $incf){
			if(is_array($incf)){
				$script = array_shift($incf);
				$attrs = '';
				foreach($incf as $attr => $val) $attrs .= " $attr=\"$val\"";
				switch(substr($script, strrpos($script, '.') + 1)){
					case 'js':
					case 'jq':
						$js .= "<script type=\"text/javscript\" src=\"$script\"$attrs></script>";
						break;
					case 'css':
						$css .= "<link rel=\"stylesheet\" href=\"$script\"$attrs />";
				}
				continue;
			}
			if($incf[0] === '<'){
				$extra .= $incf;
				continue;
			}
			switch(substr($incf, strrpos($incf, '.') + 1)){
				case 'js':
				case 'jq':
					$js .= "<script type=\"text/javascript\" src=\"$incf\"></script>";
					break;
				case 'css':
					$css .= "<link rel=\"stylesheet\" href=\"$incf\" />";
			}
		}
		
		$headervars = array_merge(array('scripts' => "$css$js$extra", 'title' => $title), $headervars);
		
		if($return) return
			$this->load_view(((boolean)$hfprefix ? "$hfprefix/" : '') . MMVC_COMMON_HEADER, $headervars, true) .
			$this->load_view($view, $vars, true) .
			$this->load_view(((boolean)$hfprefix ? "$hfprefix/" : '') . MMVC_COMMON_FOOTER, $footervars, true);
			
		return bindec(
			(string)(int)!$this->load_view(((boolean)$hfprefix ? "$hfprefix/" : '') . MMVC_COMMON_HEADER, $headervars) .
			(string)(int)!$this->load_view($view, $vars) .
			(string)(int)!$this->load_view(((boolean)$hfprefix ? "$hfprefix/" : '') . MMVC_COMMON_FOOTER, $footervars)
		);
	}
}
?>