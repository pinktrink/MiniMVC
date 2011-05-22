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
 * The database username
 */
define('MMVC_MODEL_DB_USERNM', '');
/**
 * The database password
 */
define('MMVC_MODEL_DB_PASSWD', '');
/**
 * The default database
 */
define('MMVC_MODEL_DB_DATABS', '');
/**
 * The database port number
 */
define('MMVC_MODEL_DB_PORTNM', 3306);
/**
 * The database socket
 */
define('MMVC_MODEL_DB_SOCKET', null);
/**
 * The domain name of the site
 */
define('MMVC_SITE_DOMAIN', $_SERVER['HTTP_HOST']);
?>