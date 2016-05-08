<?php

class Cache extends \Fuel\Core\Cache
{
	public static function delete_expiration($driver = array())
	{
		$cache = static::forge('__NOT_USED__', $driver);
		return is_callable(array($cache, 'delete_expiration')) ? $cache->delete_expiration() : false;
	}
}
