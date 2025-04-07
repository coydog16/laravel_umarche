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
RouteかControllerの追加設定が必要かも？（IndexRouteのリダイレクトを明示し解決）

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

2025/4/7--------------------------------------

◆Error
Adminでログアウトすると404エラー
admin.phpのadmin.welcomeをコメントアウトしたため、ログアウト後のリダイレクト先がNotFound。

1.destroyメソッドのリダイレクト先を編集（解決）

  app/Http/Controllers/Admin/Auth/AuthenticatedSessionController.php内のdestroyメソッドにおけるリダイレクト先を管理者(admin)のログイン画面に。


◆Accident
phpMyAdminの起動のためにXamppContorllerのAdminボタンが使えなかったことが地味にストレスだったため、
何とかならんかと色々調べてXamppのxampp-controll.iniを編集したらphpMyAdminが読み込まれなくなる。
ロードが終わらずいつまで待ってもviewが表示されず、120sec待機状態だったためのエラーが発生。
laravelとブラウザのキャッシュクリアやPCの再起動を試してみるも解決せず。

1.migrateコマンドでデータベース接続を確認
  php artisan migrateでデータベース接続に問題がないことを確認。
  Larabel側ではなさそう。

2.xampp-controll.iniの編集個所を復元
　MySQLのAdminボタンのリンクはApatchのポートに依存するらしいので、ServicePortsの値を8888に編集した箇所を80に書き直す。

  [ServicePorts]
　Apache=8888 => 80
　MySQL=3306

　最初に編集した時と同様に管理者権限で起動したtxtファイルで上書き保存。
  文字コードUTF-8、ANSIも試したが事象改善せず。

3.C:ドライブにxamppをインストールしてきてControll.iniを差し替える
  実施し再起動後に確認するも解決せず。
  中身を確認したら全然違っていてそりゃダメだわ。

4.Xampp再インストール
  根こそぎ方法論。腐ったミカンは箱ごと入れ替える。
  実施して起動してみたところ、今度はApatchもMySQLもStartできなくなる。
  原因はApatchとMySQLの環境変数が前のディレクトリに残っていたこと。
  以前は「E:ProgramFiles/Xampp/」にインストールしていたが、今回は「E:Xampp」とドライブ直下に。
  これによりサービスの環境変数が前のパスに残ってしまい、新規でインストールしたXammppが不具合を起こしていた模様。
  　※レジストリエディターでAmatch、MySQLそれぞれのサービスパスを設定しなおして解決

  さらに本プロジェクトのデータベースをバックアップし忘れていたため、データベース接続エラー。
  php artisa migrateはおろか、php artisan cache:clearも効かない
  データベースに関わるコマンドが壊れている模様。
  　※以前使っていたlaravel_umarcheテーブルを削除
  　　新規作成し、マイグレートを実施して解決。こういう時にSeederは本当に便利。

　開発中に環境は弄るもんじゃないということを学んだアクシデントだった。
----------------------------------------------
