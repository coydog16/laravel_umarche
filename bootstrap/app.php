<?php
 /* 
    Applicationクラスには、サービスコンテナと呼ばれる仕組みがある。
    Route、Middleware、Exceptionなどが記述されている。
 */ 
use Illuminate\Foundation\Application;

//  Exceptionクラスには例外処理が記述されている
use Illuminate\Foundation\Configuration\Exceptions;

//  Middlewareクラスはアクセス権限を管理している
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__)) // Laravelのディレクトリのパスを明示
    ->withRouting( // ルーティングの設定
        web: __DIR__.'/../routes/web.php', // WEBサイトのルート（どのページへ行くかを管理）
        commands: __DIR__.'/../routes/console.php', // コマンドのルート（プログラムを動かす命令を管理）
        health: '/up', // サーバーのヘルス管理
    )
    ->withMiddleware(function (Middleware $middleware) { // ミドルウェア設定
        //
    })
    ->withExceptions(function (Exceptions $exceptions) { //例外処理を設定
        //
    })->create(); //Laravelスタート
