<?php

// 1.6 $this->memcached
// 1.7 static::$memcached

class Cache_Storage_Memcached extends \Fuel\Core\Cache_Storage_Memcached
{
	public function __construct($identifier, $config)
	{
		parent::__construct($identifier, $config);

		if (is_object(static::$memcached))
		{
			if (isset($this->config['option']) && is_array($this->config['option']))
			{
				foreach ($this->config['option'] as $key=>$val)
				{
					try
					{
						$key = intval(str_replace('OPT_', '', $key));
						static::$memcached->setOption($key, $val);
					}
					catch(\Exception $e)
					{
						\logger(\Fuel::L_ERROR, $e->getMessage());
					}
				}
			}
		}
	}

	// get時、任意のタイミングで期限切れキャッシュを消していく
	protected function _get()
	{
		$key = $this->config['cache_id'].'.DELETE_EXPIRATION_FLG';

		if (static::$memcached->get($key)===false)
		{
			static::$memcached->set($key, true, \Config::get('cache.memcached.expiration_time', 3600));
			$this->delete_expiration();
		}

		return parent::_get();
	}

	public function delete_expiration()
	{
		// get the directory index
		$index = static::$memcached->get($this->config['cache_id']);

		if (is_array($index))
		{
			$delete_list = array();

			foreach ($index as $k=>$v)
			{
				$retry   = 0;
				$payload = false;

				while ($retry++<3 && $payload===false)
				{
					$payload = static::$memcached->get($v[0]);

					if ($payload!==false)
					{
						break;
					}

					usleep(100000);
				}

				if ($payload===false)
				{
					$delete_list[] = $k;
				}
				else
				{
					$properties_end = strpos($payload, '{{/'.static::PROPS_TAG.'}}');

					if ($properties_end!==false)
					{
						$props = json_decode(substr(substr($payload, 0, $properties_end), strlen('{{'.static::PROPS_TAG.'}}')), true);

						if ($props!==null)
						{
							$expiration = is_null($props['expiration']) ? 1 : (int) ($props['expiration'] - time());

							if ($expiration+5<0)
							{
								$delete_list[] = $k;
							}
							else
							{
								\logger(\Fuel::L_DEBUG, sprintf('LIVE: [%s] %s', date('Y-m-d H:i:s', $props['expiration']), $k));
							}
						}
					}
				}
			}

			foreach ($delete_list as $k)
			{
				unset($index[$k]);
			}
		}

		// update the directory index
		return static::$memcached->set($this->config['cache_id'], $index, 0);
	}
}
