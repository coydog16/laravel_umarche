<?php

use Illuminate\Http\Request;
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
        then: function(){
            // オーナー用のルート
            Route::middleware('web')
                ->prefix('owner')->name('owner.')
                ->group(__DIR__.'/../routes/owner.php');

            // 管理者用のルート
            Route::middleware('web')
                ->prefix('admin')->name('admin.')
                ->group(__DIR__.'/../routes/admin.php');
        }
    )
    ->withMiddleware(function (Middleware $middleware) { // ログインしていなければログイン画面にリダイレクト
        $middleware->redirectGuestsTo(function(Request $request){
            if ($request->routeIs('owner*')) {
                return $request->expectsJson() ? null : route('owner.login');
            }
            if ($request->routeIs('admin*')) {
                return $request->expectsJson() ? null : route('admin.login');
            }
            return $request->expectsJson() ? null : route('login');
        });
    })

    
    ->withExceptions(function (Exceptions $exceptions) { //例外処理を設定
    })->create(); //Laravelスタート
