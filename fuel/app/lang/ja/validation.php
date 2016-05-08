<?php
/**
 * Japanese language package for FuelPHP.
 *
 * @package    JaLangPack
 * @version    1.0
 * @author     sharkpp
 * @license    MIT License
 * @copyright  2013+ sharkpp
 * @link       https://www.sharkpp.net/
 */

return array(
	'required'        => ':label が入力されていません。',
	'min_length'      => ':label は :param:1 文字以上で入力してください。',
	'max_length'      => ':label は :param:1 文字以内で入力してください。',
	'exact_length'    => ':label は :param:1 文字で入力してください。',
	'match_value'     => ':label は :param:1 を含めて入力する必要があります。',
	'match_pattern'   => ':label を正しく入力してください。',
	'match_field'     => ':label は :param:1 と一致する必要があります。',
	'valid_email'     => ':label は有効なメールアドレスではありません。',
	'valid_emails'    => ':label は有効なメールアドレスではありません。',
	'valid_url'       => ':label は有効なURLではありません。',
	'valid_ip'        => ':label は有効なIPではありません。',
	'numeric_min'     => ':param:1 より小さい値は :label に入力できません。',
	'numeric_max'     => ':param:1 より大きい値は :label に入力できません。',
	'numeric_between' => ':param:1 から :param:2 までの値を :label に入力してください。',
	'valid_string'    => ':label に正しい値を入力してください。',
	'valid_date'      => ':label に正しい日付を入力してください。',
	'required_with'   => ':param:1 が入力されている場合には :label も入力をする必要があります。',

	// ここからカスタムバリデーション
	'mb_trim'         => 'mb_trim',
	'is_hiragana'     => ':label はひらがなで入力してください。',
);
