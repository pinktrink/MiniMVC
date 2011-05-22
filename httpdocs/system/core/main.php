<?php
/**
 * MiniMVC Framework for PHP
 *
 * This little system(7 files, 6 folders) will replicate the CodeIgniter
 * method of handling URLs and will provide the absolute minimal basis
 * for working with an MVC framework.  IF you don't want the database
 * object included in a model, on __construct call
 * parent::__construct(false) to prevent the MySQLi object from being
 * loaded into the model.
 * This framework is seriously so stupid simple, but it works.
 *
 * @copyright Copyright (c) 2010, Eric Kever
 * @author Eric Kever <ekever@reusserdesign.com
 */

/**
 * The location of the core directory for MiniMVC
 * This will not need to be changed.
 */
define('MMVC_CORE_DIRECTORY', dirname(__FILE__));
/**
 * The location of the sys directory for MiniMVC
 * This will not need to be changed.
 */
define('MMVC_SYS_DIRECTORY', dirname(MMVC_CORE_DIRECTORY));
/**
 * The site root for MiniMVC
 * This will not need to be changed.
 */
define('MMVC_SITE_ROOT', dirname(MMVC_SYS_DIRECTORY));
/**
 * The location of the include directory for MiniMVC
 * This will not need to be changed.
 */
define('MMVC_INC_DIRECTORY', MMVC_SYS_DIRECTORY . '/includes');
/**
 * The location of the models directory for MiniMVC
 * This will not need to be changed.
 */
define('MMVC_MODEL_DIRECTORY', MMVC_SYS_DIRECTORY . '/models');
/**
 * The location of the controllers directory for MiniMVC
 * This will not need to be changed.
 */
define('MMVC_CONTROLLER_DIRECTORY', MMVC_SYS_DIRECTORY . '/controllers');
/**
 * The location of the view directory for MiniMVC
 * This will not need to be changed.
 */
define('MMVC_VIEW_DIRECTORY', MMVC_SYS_DIRECTORY . '/views');

define('TYPE_STRING', gettype((string)NULL));
define('TYPE_BOOL', gettype((bool)NULL));
define('TYPE_INT', gettype((int)NULL));
define('TYPE_FLOAT', gettype((float)NULL));
define('TYPE_ARRAY', gettype((array)NULL));
define('TYPE_OBJECT', gettype((object)NULL));
define('TYPE_NULL', gettype(NULL));

include MMVC_CORE_DIRECTORY . '/base.php';
include MMVC_CORE_DIRECTORY . '/model.php';
include MMVC_CORE_DIRECTORY . '/controller.php';

/**
 * e404
 *
 * Send the 404 header to tell the user that they screwed up
 *
 * @author Eric Kever <ekever@reusserdesign.com>
 * @return void
 */
function e404(){
	//header("HTTP/1.0 404 Not Found");
	header('Location: /membership/renew');
	die;
}

/**
 * redirect
 *
 * Send the Location header to redirect the user to a different location
 *
 * @author Eric Kever <ekever@reusserdesign.com>
 * @param string $location The URL to send the user to
 * @return void
 */
function redirect($location){
	header("Location: $location");
	die;
}

/**
 * method_args_required
 *
 * Returns the number of arguments a specific method requires.
 *
 * @author Eric Kever <ekever@reusserdesign.com>
 * @param object $obj The object that holds the method
 * @param string $method The method to examine
 * @return int|false
 */
function method_args_required($obj, $method){
	$reflect = new ReflectionMethod($obj, $method);
	return $reflect->getNumberOfRequiredParameters();
}

/**
 * out
 *
 * Echos a variable if it is set (strict safe).  Optionally, if the variable is not set, can echo another string.  Always returns 1.
 *
 * @author Eric Kever <ekever@reusserdesign.com>
 * @param var &$var The variable to check for existence.  This can only be a variable.
 * @param string|int $else The variable to echo if the first does not exist.
 * @return int
 */
function out(&$var, $else = NULL){
	if(isset($var)) return print $var;
	return print (string)$else;
}

/**
 * URI Handling
 * Perform a bunch of magic with the URI to tell the system exactly what to do, then off we go
 */
$isindex = false;
$req_uri = explode('/', trim(str_replace(array('../', '/..'), '', $_SERVER['REQUEST_URI']), '/'));
$entry_uri = $comp_uri = MMVC_CONTROLLER_DIRECTORY;
$entry_obj = '';
$entry_mth = '';
$entry_arg = array();
$mreplace = array(
	'-' => '_',
	'.' => '_dot_'
);

if($req_uri === (array)''){
	$entry_uri .= '/index.php';
	$entry_obj = '_index';
	$isindex = true;
}else{
	while((boolean)($uri_fragment = array_shift($req_uri))){
		if(is_dir("$entry_uri/$uri_fragment")){
			$entry_uri .= "/$uri_fragment";
			continue;
		}elseif(is_file("$entry_uri/$uri_fragment.php")){
			$entry_obj = str_replace(
				array_keys($mreplace),
				array_values($mreplace),
				$uri_fragment
			);
			$entry_uri .= "/$uri_fragment.php";
		}elseif(is_file("$entry_uri.php")){
			array_unshift($req_uri, $uri_fragment);
			$s = strrpos($entry_uri, '/');
			$entry_obj = str_replace(
				array_keys($mreplace),
				array_values($mreplace),
				substr($entry_uri, ($s !== false ? $s : -1) + 1)
			);
			$entry_uri .= ".php";
		}
		break;
	}
	
	if(is_file("$entry_uri.php")){
		$s = strrpos($entry_uri,'/');
		$entry_obj = str_replace(
			array_keys($mreplace),
			array_values($mreplace),
			substr($entry_uri, ($s !== false ? $s : -1 ) + 1)
		);
		$entry_uri .= '.php';
	}
	if($entry_uri === $comp_uri) $entry_uri .= '/index.php';
}

if(!is_file($entry_uri)) e404();
include $entry_uri;
$loader = new $entry_obj;
if($isindex){
	if(!method_exists($loader, '_index') || !is_callable(array($loader, '_index'))) e404();
	$loader->_index();
	return;
}

if((boolean)($uri_fragment = array_shift($req_uri))){
	$entry_mth = ($uri_fragment === $entry_obj ? '_' : '') .
	 str_replace(array_keys($mreplace), array_values($mreplace), $uri_fragment);
	while((boolean)($entry_arg[] = array_shift($req_uri)));
	array_pop($entry_arg);
	if(is_numeric($entry_mth) && ((int)$entry_mth > 0 || $entry_mth == '0')){
		if(isset($loader->numfuncs[(int)$entry_mth])){
			$entry_num_mth = $loader->numfuncs[(int)$entry_mth];
			$numreq = method_args_required($loader, $entry_num_mth);
			$isinfinite = (boolean)(isset($loader->infinite, $loader->infinite[$entry_num_mth]) && $loader->infinite[$entry_num_mth]);
			if($isinfinite){
				if(count($entry_arg) < $numreq) e404();
			}else if(count($entry_arg) !== $numreq) e404();
			call_user_func_array(array($loader, $entry_num_mth), $entry_arg);
			return;
		}
		else e404();
	}
	
	if(!method_exists($loader, $entry_mth) || !is_callable(array($loader, $entry_mth))) e404();
	$numreq = method_args_required($loader, $entry_mth);
	$isinfinite = (boolean)(isset($loader->infinite, $loader->infinite[$entry_mth]) && $loader->infinite[$entry_mth]);
	if($isinfinite){
		if(count($entry_arg) < $numreq) e404();
	}else if(count($entry_arg) !== $numreq) e404();
	call_user_func_array(array($loader, $entry_mth), $entry_arg);
}else{
	$method = '_' . str_replace(array_keys($mreplace), array_values($mreplace), $entry_obj);
	if(!method_exists($loader, $method) || !is_callable(array($loader, $method))) e404();
	$loader->$method();
}
?>