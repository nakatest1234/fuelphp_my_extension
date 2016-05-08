<?php

class Validation extends \Fuel\Core\Validation
{
	// マルチバイトtrim
	public function _validation_mb_trim($val)
	{
		return \MyFunc::mb_trim($val);
	}

	// ひらがなチェック
	public function _validation_is_hiragana($val)
	{
		return \MyFunc::is_hiragana($val);
	}

	// 引数で mb_convert_kana 実行
	public function _validation_conv_all($val, $flg)
	{
		return mb_convert_kana($val, $flg, \Config::get('encoding', mb_internal_encoding()));
	}
}
