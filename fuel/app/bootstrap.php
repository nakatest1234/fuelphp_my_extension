<?php

// Load in the Autoloader
require COREPATH.'classes'.DIRECTORY_SEPARATOR.'autoloader.php';
class_alias('Fuel\\Core\\Autoloader', 'Autoloader');

// Bootstrap the framework DO NOT edit this
require COREPATH.'bootstrap.php';

Autoloader::add_classes(array(
	// Add classes you want to override here
	// Example: 'View' => APPPATH.'classes/view.php',
	'Log'                     => APPPATH.'classes/MyClass/log.php',
	'Validation'              => APPPATH.'classes/MyClass/validation.php',
	'MyCache'                 => APPPATH.'classes/MyClass/mycache.php',
	'Cache'                   => APPPATH.'classes/MyClass/cache.php',
	'Cache_Storage_Memcached' => APPPATH.'classes/MyClass/cache/storage/memcached.php',
	'Session_Memcached'       => APPPATH.'classes/MyClass/session/memcached.php',
));

// Register the autoloader
Autoloader::register();

/**
 * Your environment.  Can be set to any of the following:
 *
 * Fuel::DEVELOPMENT
 * Fuel::TEST
 * Fuel::STAGING
 * Fuel::PRODUCTION
 */
Fuel::$env = (isset($_SERVER['FUEL_ENV']) ? $_SERVER['FUEL_ENV'] : Fuel::DEVELOPMENT);

// Initialize the framework with the config file.
Fuel::init('config.php');
