# cheetan - ちいたん

オリジナルのプロジェクトページ  
[https://ja.osdn.net/projects/cheetan/](https://ja.osdn.net/projects/cheetan/)

残念ながら、開発は中止されたようです。  

# はじめに
- 外部公開しない、簡単なツール
- サーバー側にデータを送信、レスポンスを受け取るJavaScriptライブラリーの動作確認

上記のことを行いたい場合、
- 素のPHPだとデータベースの接続を忘れたので調べないといけない
- かといってLaravelのようなフルスタック・フレームワークは必要ない

というジレンマが発生すると思います。  

# 目標
- webアプリケーションを作るにあたり、最小限度の機能があればよい
- 配置を楽にするため、ファイルの数は少ない方が良い(可能であれば1ファイル)　　

Python製のフレームワーク[Bottle](https://bottlepy.org/docs/dev/)のような、簡単にサンプルが作成できるフレームワークとしたい。

# 方針
オリジナルの思想は受け継ぎつつ、下位互換のない変更を加えていきます。

- 最小構成(可能であれば1ファイル)にしたい chetan.phpのみが目標
- サンプルプログラムの追加
- テストの追加

# バージョン0.9系で対応

- configは関数ではなくarray形式の設定ファイルに変更
- バージョン表記を3桁に変更
- 関数名を正規(camelCaseに統一)
- データベースアクセスはPDOのみに一本化
- textsqlの廃止
- jsonのサポート
- sqlite3の正式サポート追加
- Postgresの動作確認
- SQLiteの動作確認


# 各種PDOのDSN名とインストール

#### MySQL

DSN名は「mysql」

`$ sudo apt install php-mysql`


#### PostgreSQL

DSN名は「pgsql」

`$ sudo apt install php-pgsql`


#### SQLite3

DSN名は「sqlite」

`$ sudo apt install php-sqlite3`

