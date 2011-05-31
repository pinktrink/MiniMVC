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
/**
 * The word size of the system
 * This will not need to be changed.
 */
define('MMVC_WORD_SIZE', ((int)log(PHP_INT_MAX + 1, 2) + 1));

if(!defined('TYPE_STRING')) define('TYPE_STRING', gettype((string)NULL));
if(!defined('TYPE_BOOL')) define('TYPE_BOOL', gettype((bool)NULL));
if(!defined('TYPE_INT')) define('TYPE_INT', gettype((int)NULL));
if(!defined('TYPE_FLOAT')) define('TYPE_FLOAT', gettype((float)NULL));
if(!defined('TYPE_ARRAY')) define('TYPE_ARRAY', gettype((array)NULL));
if(!defined('TYPE_OBJECT')) define('TYPE_OBJECT', gettype((object)NULL));
if(!defined('TYPE_NULL')) define('TYPE_NULL', gettype(NULL));

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
$entry_dir = MMVC_CONTROLLER_DIRECTORY;
$entry_file = MMVC_DEFAULT_FILE;
$entry_obj = MMVC_DEFAULT_CLASS;
$entry_mth = MMVC_DEFAULT_METHOD;

while($uri_fragment = array_shift($req_uri)){
	if(file_exists("$uri_fragment." . MMVC_DEFAULT_EXTENSION)){
		$entry_file = "$uri_fragment." . MMVC_DEFAULT_EXTENSION;
		break;
	}elseif(is_dir($uri_fragment)){
		$entry_dir .= "/$uri_fragment";
		continue;
	}
	array_unshift($req_uri, $uri_fragment);
}
$entry = "$entry_dir/$entry_file";

if(!is_file($entry)) e404();
include $entry;
if(class_exists($entry_obj)) e404();
$loader = new $entry_obj();

if($uri_fragment = array_shift($req_uri) && method_exists($loader, $uri_fragment))
	if(is_numeric($uri_fragment)){
		if(!isset($loader->numfuncs[(int)$entry_mth]) &&
		   method_exists($loader, ($numfunc = $loader->numfuncs[(int)$entry_mth])) &&
		   $entry_args[] = $numfunc) e404();
	}else{
		if(!method_exists($loader, $uri_fragment)) e404();
		$entry_mth = $uri_fragment;
	}
else
	array_unshift($req_uri, $uri_fragment);
//The rest of $req_uri is args.

//Enjoy the ride!
call_user_func_array(array($loader, $entry_mth), $req_uri);
?>