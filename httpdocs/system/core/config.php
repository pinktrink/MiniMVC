<?php
/**
 * MiniMVC config.php
 * 
 * The MiniMVC configuration file
 * 
 * @copyright Copyright (c) 2010, Eric Kever
 * @author Eric Kever
 */
/**
 * The location of the MySQL database server
 */
define('MMVC_MODEL_DB_SERVER', 'localhost');
/**
 * The database type (YOU must ensure that the PDO driver is available!)
 */
define('MMVC_MODEL_DB_TYPE', 'mysql');
/**
 * The database username
 */
define('MMVC_MODEL_DB_USERNAME', '');
/**
 * The database password
 */
define('MMVC_MODEL_DB_PASSWORD', '');
/**
 * The default database
 */
define('MMVC_MODEL_DB_DATABASE', '');
/**
 * The database port number
 */
define('MMVC_MODEL_DB_PORT', 3306);
/**
 * The database socket
 */
define('MMVC_MODEL_DB_SOCKET', null);
/**
 * The domain name of the site
 */
define('MMVC_SITE_DOMAIN', $_SERVER['HTTP_HOST']);
/**
 * The common header name
 *
 * This will be the header name to load when using load_hf_view, minus .php.
 * The directory a given common header can be configured when calling load_hf_view.
 * Consult the load_hf_view documentation for more information.
 */
define('MMVC_COMMON_HEADER', '_header');
/**
 * The common footer name
 *
 * This will be the footer name to load when using load_hf_view, minus .php.
 * The directory a given common footer can be configured when calling load_hf_view.
 * Consult the load_hf_view documentation for more information.
 */
define('MMVC_COMMON_FOOTER', '_footer');

define('MMVC_DEFAULT_FILE', 'index');
define('MMVC_DEFAULT_CLASS', 'index');
define('MMVC_DEFAULT_METHOD', 'index');
define('MMVC_DEFAULT_EXTENSION', 'php');
$method_replace = array(
	'-' => '_',
	'.' => '_dot_'
);
?>