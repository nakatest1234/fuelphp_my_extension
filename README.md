fuephp_my_extension
===================

FeulPHPの独自拡張

* 拡張内容
  1. validationの言語ファイル
  2. validationの拡張
    1. mb_trim
    2. is_hiragana
  3. memcachedのオプションを設定出来るようにcore拡張

* FuelPHPをcloneして初期化する

  ```
  git clone --recursive git@github.com:fuel/fuel.git
  php composer.phar update
  php oil refine install
  ```

* このリポジトリのファイルを展開するだけ
