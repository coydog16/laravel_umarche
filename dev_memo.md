2025/4/5--------------------------------------
◆Error
ログイン画面でリダイレクトバグ発生
事象：パスワードを入力してもdashboardに移行せず、login画面にリダイレクトされる
owner,adminではログインできないが、userではdashboardにログイン可能

route/owner.phpの14行目、Route::middlwareメソッドを修正することで解決。

修正前
14 | Route::middleware('auth')->group(function () {　});

修正後
14 | Route::middleware('auth:owner')->group(function () {　});

----------------------------------------------



2025/4/6--------------------------------------

◆Error
AdminDashboardに新たにナビゲーションを制作し、ルートを作成。
オーナ一覧画面が正しく表示されない。

Exception:Call to undefined method App\Http\Controllers\Admin\OwnersController::middleware()



1. OwnersController.phpのクラスを確認（済）
  Laravel のコントローラーでミドルウェアを使用する場合、Controller クラスが
  Illuminate\Routing\Controller を継承している必要がある。
  OwnersController が正しいクラスを継承しているか確認。

  class OwnersController extends Controller


2. OwnersController.phpの$this->middleware() コードが正しいか確認。（済）

    public function __construct()
    {
      $this->middleware('auth:admins');
    }


3. ミドルウェアが正しく動作しているか確認。（済）

  public function __construct()
  {
    dd('ミドルウェアが適用されました');
    $this->middleware('auth:admins');
  }

  dd()のヘルパ関数で問題なく表示される


4. キャッシュクリアコマンドを実施（済）

  php artisan config:clear
  php artisan cache:clear
  php artisan route:clear


5.Http/Controllers/Controller.phpの継承を確認（解決）
  namespace App\Http\Controllers;
  
  use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
  use Illuminate\Foundation\Bus\DispatchesJobs;
  use Illuminate\Foundation\Validation\ValidatesRequests;
  use Illuminate\Routing\Controller as BaseController;
  
  class Controller extends BaseController
  {
   use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
  }
 

◆Error
Seederを作成しリフレッシュマイグレートで作成しなおした際にSQL側でエラー

Illuminate\Database\QueryException
SQLSTATE[42S02]: Base table or view not found: 1146 Table 'laravel_umarche.admins' doesn't exist (Connection: mysql, SQL: insert into admins (name, email, password, created_at) values (test, test@test.com, $2y$12$79aSMyKvJiObLfupUisB2eM6nMhal8pLcjy2cGhKnuyA.w.34zt.i, 2023/08/25 22:45:40))
※Adminsテーブルを作成するマイグレートが存在しない

1.adminテーブルを作成するファイル（migration/2025_04_05_010501_create_admins_table.php）に誤記がないか確認

createメソッドの引数がownersになっていることを確認。修正し解決。

修正前
        if (!Schema::hasTable('owners')) {
            Schema::create('ownser', function (Blueprint $table) {
            })
        }

修正後
        if (!Schema::hasTable('admins')) {
            Schema::create('admins', function (Blueprint $table) {
            })
        }


◆Error
Collectionのテストコードを記載したところ、/admin/owners/Indexのviewが真っ白で何も表示されなくなった
（エラーメッセージもなし）

  ==code==

    $e_all = Owner::all();
    $q_get = DB::table('owners')->select('name')->get();
    $q_first = DB::table('owners')->select('name')->first();

    $c_test = collect([
        'name' => 'てすと'
    ]);

    dd($e_all, $q_get, $q_first, $c_test);

  ======

  1.書いたコードをコメントアウトし、dd()でテスト（済）
    問題なく表示される

  2.Indexではなく、DashboardのRouteからジャンプ（解決）
    
    Exception:class "APP\Models\Owner;" not found
    記載ミス発覚
    ※APPが大文字

    Exception:Class "Illminate\Support\Facades\DB" not found
    記載ミス発覚
    ※Illminate（×）Illuminate（〇）

URLを/admin/ownersに、記載ミス2点修正で解決したが/admin/owners/indexはviewが設定されていないのか真っ白のまま
RouteかControllerの追加設定が必要かも？

◆Error
create.blade.phpを新規作成するが、TailblocksのCSSが機能しない

  1.親要素に別のCSSが機能しているか確認。
    Chromeの検証モードで中身をチェックするも、それらしいCSSなし。
　  layouts/app.blade.phpの中身までチェックするがそれらしいCSSなし。

  2.キャッシュクリア
    cmdでキャッシュクリアコマンドを実行し解決
      php artisan cache:clear
      php artisan view:clear

----------------------------------------------