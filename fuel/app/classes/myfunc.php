<?php

// 独自関数

class MyFunc
{
	// マルチバイトトリム
	public static function mb_trim($str)
	{
		static $whitespace = '[\s\0\x0b\p{Zs}\p{Zl}\p{Zp}]';

		$str = strval($str);

		return $str==='' ? '' : preg_replace(sprintf('/(^%s+|%s+$)/u', $whitespace, $whitespace), '', $str);
	}

	// ひらがなチェック
	public static function is_hiragana($str)
	{
		$str = strval($str);

		if ($str==='') return true;

		$str = mb_convert_encoding($str, 'UTF-8');

		return (preg_match('/^(?:\xE3\x81[\x81-\xBF]|\xE3\x82[\x80-\x93]| |　|ー)+$/', $str)) ? true : false;
	}

	// 出力待つ場合に使う
	// is_background | false | 返却値取得
	//               | true  | 非同期実行(返却値取れない)
	// $timeout      | 0     | タイムアウト設定無し(s)
	// Windwosはバックグラウンドやタイムアウト設定無効。おとなしく待て
	public static function exec($cmd, $args='', $is_background=false, $stdin='', $cwd=null, $env=array(), $timeout=0)
	{
		$buf_size = 4096;
		$_WIN     = DIRECTORY_SEPARATOR==='\\';
		$_stdin   = is_array($stdin) ? $stdin : explode(PHP_EOL, $stdin);
		$_stdout  = '';
		$_stderr  = '';

		$result = array(
			'status' => -1,
			'stdin'  => $stdin,
			'stdout' => '',
			'stderr' => '',
		);

		if ( ! is_executable($cmd))
		{
			$result['stderr'] = 'NOT EXECUTABLE';
			return $result;
		}

		$descriptorspec = array(
			0 => array('pipe', 'r'), // stdin
			1 => array('pipe', 'w'), // stdout
			2 => array('pipe', 'w'), // stderr
		);

		$cmd_merge = sprintf('%s %s', $cmd, $args);

		// バックグラウンド実行
		if ($is_background && ! preg_match('/&$/', $cmd_merge))
		{
			$cmd_merge .= ' &';
		}
		// log for FuelPHP
		//\logger(\Fuel::L_DEBUG, $cmd_merge);

		try
		{
			$other_optuons = array();

			// for Windows
			if ($_WIN)
			{
				if ($timeout>0)
				{
					$timeout = ini_get('max_execution_time');
				}

				foreach (array_keys($_SERVER) as $k)
				{
					if (preg_match('/^SystemRoot$/i', $k))
					{
						$env = array_merge($env, array($k=>$_SERVER[$k]));
						break;
					}
				}

				$other_optuons = array(
					'suppress_errors' => true,
					'bypass_shell'    => true,
				);
			}

			$process = proc_open($cmd_merge, $descriptorspec, $pipes, $cwd, $env, $other_optuons);

			if (is_resource($process))
			{
				// STDIN
				foreach ($_stdin as $v)
				{
					fwrite($pipes[0], $v);
				}
				fclose($pipes[0]);

				if ($is_background===false)
				{
					// STDOUT, STDERR
					if ($timeout>0)
					{
						stream_set_blocking($pipes[1], 0); // 0:非同期, 1:同期
						stream_set_blocking($pipes[2], 0); // 0:非同期, 1:同期

						$timeout_max = time() + $timeout + 1;

						while (feof($pipes[1])===false || feof($pipes[2])===false)
						{
							$read   = array($pipes[1], $pipes[2]);
							$write  = null;
							$except = null;
							$_timeout = ($timeout_max-time())>0 ? $timeout_max-time() : 1;

							$ret = stream_select($read, $write, $except, $_timeout);

							if ($ret===false)
							{
								proc_terminate($process); // error
								break;
							}
							else if ($ret===0)
							{
								proc_terminate($process); // timeout
								break;
							}
							else
							{
								foreach ($read as $sock)
								{
									if ($sock===$pipes[1]) {
										$_stdout .= fread($sock, $buf_size);
									}
									else if ($sock===$pipes[2])
									{
										$_stderr .= fread($sock, $buf_size);
									}
								}
							}
						}

						fclose($pipes[1]);
						fclose($pipes[2]);
					}
					else
					{
						while( ! feof($pipes[1]))
						{
							$_stdout .= fgets($pipes[1], $buf_size);
						}
						fclose($pipes[1]);

						while( ! feof($pipes[2]))
						{
							$_stderr .= fgets($pipes[2], $buf_size);
						}
						fclose($pipes[2]);
					}
				}

				$result['status'] = proc_close($process);
				$result['stdout'] = $_stdout;
				$result['stderr'] = $_stderr;
			}
		}
		catch (\Exception $e)
		{
			$result['stderr'] = $e->getMessage();
		}

		return $result;
	}
}
