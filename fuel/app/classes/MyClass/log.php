<?php

class Log extends Fuel\Core\Log
{
	protected static $PWD = null;

	/**
	 * 改行を無くす
	 * FuelPHPがregister_shutdown_functionで登録した関数でlogger呼び出ししてるので、
	 * そちらでも改行を無くすためcallbackを使う
	 * @param array $record
	 * @return array $record
	 */
	public static function nr2line($record)
	{
		$record['message'] = str_replace(array("\r\n", "\r", "\n"), ' ', $record['message']);

		return $record;
	}

	/**
	 * 親でstatic::$monolog定義や書き込み先ハンドラ定義してるので、その後callback設定
	 */
	public static function _init()
	{
		parent::_init();

		static::$monolog->pushProcessor(__CLASS__.'::nr2line');
	}

	private static function plus_backtrace($msg='')
	{
		if (is_null(self::$PWD))
		{
			self::$PWD = \Fuel::$is_cli ? DOCROOT : realpath(DOCROOT.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
		}

		$debug = array();

		$file = '';
		$line = 0;
		$func_name  = '';
		$class_name = '';

		if (PHP_VERSION < '5.3.6')
		{
			$debug = debug_backtrace();
		} 
		else if (PHP_VERSION < '5.4')
		{
			$debug = debug_backtrace(FALSE);
		}
		else
		{
			$debug = debug_backtrace(FALSE, 4);
		}

		foreach ($debug as $i=>$val)
		{
			if ((isset($val['class']) && $val['class']===__CLASS__) && (isset($val['function']) && $val['function']!=='var_dump')) continue;

			$file       = isset($val['file']    ) ? $val['file']         : '';
			$line       = isset($val['line']    ) ? intval($val['line']) : 0;
			$func_name  = isset($debug[$i+1]['function']) ? $debug[$i+1]['function'] : '';
			$class_name = isset($debug[$i+1]['class']   ) ? $debug[$i+1]['class']    : '';

			$tmp = '';

			if ($class_name==='')
			{
				if ($func_name!=='')
				{
					$tmp = " [{$func_name}]";
				}
			}
			else
			{
				if ($func_name==='')
				{
					$tmp = " [{$class_name}]";
				}
				else
				{
					$tmp = " [{$class_name}->{$func_name}]";
				}
			}

			$msg = sprintf('[%s]:%d%s %s', str_replace(self::$PWD, '', $file), $line, $tmp, $msg);

			break;
		}

		return $msg;
	}

	public static function write($level, $msg, $method = null)
	{
		return parent::write($level, self::plus_backtrace($msg), $method);
	}

	public static function var_dump($data)
	{
		ob_start();
		var_dump($data);
		return parent::write(\Fuel::L_DEBUG, self::plus_backtrace(ob_get_clean()));
	}
}
