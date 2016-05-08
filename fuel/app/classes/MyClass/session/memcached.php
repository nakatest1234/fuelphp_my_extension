<?php

class Session_Memcached extends \Fuel\Core\Session_Memcached
{
	public function init()
	{
		parent::init();

		if (is_object($this->memcached))
		{
			if (isset($this->config['option']) && is_array($this->config['option']))
			{
				foreach ($this->config['option'] as $key=>$val)
				{
					try
					{
						$key = intval(str_replace('OPT_', '', $key));
						$this->memcached->setOption($key, $val);
					}
					catch(\Exception $e)
					{
						logger(\Fuel::L_ERROR, $e->getMessage());
					}
				}
			}
		}
	}
}
