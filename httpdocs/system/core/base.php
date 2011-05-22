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
			$this->load_view(((boolean)$hfprefix ? "$hfprefix/" : '') . '_header', $headervars, true) .
			$this->load_view($view, $vars, true) .
			$this->load_view(((boolean)$hfprefix ? "$hfprefix/" : '') . '_footer', $footervars, true);
			
		return bindec(
			(string)(int)!$this->load_view(((boolean)$hfprefix ? "$hfprefix/" : '') . '_header', $headervars) .
			(string)(int)!$this->load_view($view, $vars) .
			(string)(int)!$this->load_view(((boolean)$hfprefix ? "$hfprefix/" : '') . '_footer', $footervars)
		);
	}
}
?>