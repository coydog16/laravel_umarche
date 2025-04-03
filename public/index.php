<?php

/* 
　  クライアントからのリクエストで最初に読み込まれるページ。
    エントリーポイントとも呼ばれる。
    このページからミドルウェア（サービスコンテナやサービスプロバイダなど）を読み込み、
    ルーティングからコントローラーへ処理が進む。
*/

/* 
    use文について
    Laravel8までは【use Illuminate\Constracts\Http\Kernel】でKernelファイルを読み込んでいた。
    Laravel10から/bootstrap/app.phpのApplicatiopnクラスのメソッドに統合された。
*/

// HTTPリクエストを表し、リクエストデータ（GET,POST,ヘッダーなど）を扱うために使用される。
use Illuminate\Http\Request;

// Laravelの処理開始時間を記録するメソッド。アプリケーションのパフォーマンスを測定する定数。
define('LARAVEL_START', microtime(true)); 

// メンテナンスモードかどうかの確認。trueだった場合は、maintenance.phpが実行され、通常のリクエスト処理は停止する
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

/*  
    Composerオートローダーの登録。autoload.phpを実行し、Laravelのコアクラスやアプリケーションクラスをロードする。
    requireなしで別ファイルのクラスを利用可能。(NameSpaseやuse文など)
*/
require __DIR__.'/../vendor/autoload.php';

// handlRequestメソッドを呼び出し、HTTPリクエストを処理する。
(require_once __DIR__.'/../bootstrap/app.php')
    ->handleRequest(Request::capture());
