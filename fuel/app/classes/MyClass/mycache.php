<?php

class MyCache
{
	private   static $cache    = null;
	protected static $cache_id = null;

	public static function forge($config = array())
	{
		static $instance = null;

		if ($instance===null) 
		{
			if ( ! class_exists('Memcached') )
			{
				throw new \FuelException('Memcached extension error');
			}

			if ( ! empty($config) && ! is_array($config) && ! is_null($config))
			{
				$config = array('driver' => $config);
			}

			\Config::load('cache', true);

			$config = array_merge(\Config::get('cache', array()), (array) $config);

			if (empty($config['memcached']))
			{
				throw new \FuelException('No cache driver given or no default cache driver set.');
			}

			if (self::$cache===null) 
			{
				$servers = isset($config['memcached']['servers']) ? $config['memcached']['servers'] : array();

				if ( empty($servers) OR ! is_array($servers))
				{
					$servers = array('default' => array('host' => '127.0.0.1', 'port' => '11211'));
				}

				foreach ($servers as $key=>$conf)
				{
					if ( ! isset($conf['host']) OR ! is_string($conf['host']))
					{
						throw new \FuelException('Invalid Memcached server definition in the cache configuration.');
					}
					if ( ! isset($conf['port']) OR ! is_numeric($conf['port']) OR $conf['port'] < 1025 OR $conf['port'] > 65535)
					{
						throw new \FuelException('Invalid Memcached server definition in the cache configuration.');
					}
					if ( ! isset($conf['weight']) OR ! is_numeric($conf['weight']) OR $conf['weight'] < 0)
					{
						// set a default
						$servers[$key]['weight'] = 0;
					}
				}

				self::$cache = new \Memcached();

				self::$cache->addServers($servers);

				if (self::$cache->getVersion()===false)
				{
					throw new \FuelException('Memcached cache are configured, but there is no connection possible. Check your configuration.');
				}

				self::$cache_id = isset($config['memcached']['cache_id']) ? $config['memcached']['cache_id'] . '__' : '';

				if (isset($config['option']) && is_array($config['option']))
				{
					foreach ($config['option'] as $key=>$val)
					{
						try
						{
							$key = intval(str_replace('OPT_', '', $key));
							self::$cache->setOption($key, $val);
						}
						catch(\Exception $e)
						{
							logger(\Fuel::L_ERROR, $e->getMessage());
						}
					}
				}

				$instance = new self;
			}
		}

		return $instance;
	}

	public static function get_connection()
	{
		return self::$cache;
	}

	public static function get_id($key)
	{
		return self::$cache_id . $key;
	}

	public static function set($key, $val, $expire=0)
	{
		$cache = self::forge();

		return $cache->get_connection()->set($cache->get_id($key), $val, $expire);
	}

	public static function get($key)
	{
		$cache = self::forge();

		$val = $cache->get_connection()->get($cache->get_id($key));

		if ($cache->get_connection()->getResultCode()!==\Memcached::RES_SUCCESS)
		{
			throw new \CacheNotFoundException('not found');
		}

		return $val;
	}

	public static function delete($key)
	{
		$cache = self::forge();

		return $cache->get_connection()->delete($cache->get_id($key));
	}

	public static function touch($key, $expire=0)
	{
		$cache = self::forge();

		return $cache->get_connection()->touch($cache->get_id($key), $expire);
	}

	public static function delete_all()
	{
		$cache = self::forge();

		$prefix = $cache->get_id($key);

		$keys = $cache->get_connection()->getAllKeys();

		if (is_array($keys))
		{
			foreach ($keys as $key)
			{
				if (preg_match('/^'. preg_quote($prefix) . '(.*)$/', $key, $matches))
				{
					self::delete($matches[1]);
				}
			}
		}
	}
}
