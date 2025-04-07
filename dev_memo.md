2025/4/5--------------------------------------
◆Error
ログイン画面でリダイレクトバグ発生
事象：パスワードを入力してもdashboardに移行せず、login画面にリダイレクトされる
owner, adminではログインできないが、userではdashboardにログイン可能

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

Exception: Call to undefined method App\Http\Controllers\Admin\OwnersController::middleware()

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

5. Http/Controllers/Controller.phpの継承を確認（解決）
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

  1. 書いたコードをコメントアウトし、dd()でテスト（済）
    問題なく表示される

  2. Indexではなく、DashboardのRouteからジャンプ（解決）
    
    Exception:class "APP\Models\Owner;" not found
    記載ミス発覚
    ※APPが大文字

    Exception:Class "Illminate\Support\Facades\DB" not found
    記載ミス発覚
    ※Illminate（×）Illuminate（〇）

  URLを/admin/ownersに、記載ミス2点修正で解決したが/admin/owners/indexはviewが設定されていないのか真っ白のまま
  RouteかControllerの追加設定が必要かも？（IndexRouteのリダイレクトを明示し解決）

◆解決

◆Error
create.blade.phpを新規作成するが、TailblocksのCSSが機能しない

  1. 親要素に別のCSSが機能しているか確認。
    Chromeの検証モードで中身をチェックするも、それらしいCSSなし。

　  layouts/app.blade.phpの中身までチェックするがそれらしいCSSなし。

  2.キャッシュクリア

    cmdでキャッシュクリアコマンドを実行し解決
      php artisan cache:clear
      php artisan view:clear

◆解決

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

3. C:ドライブにxamppをインストールしてきてControll.iniを差し替える
  実施し再起動後に確認するも解決せず。
  中身を確認したら全然違っていてそりゃダメだわ。

4. Xampp再インストール
  根こそぎ方法論。腐ったミカンは箱ごと入れ替える。
  実施して起動してみたところ、今度はApatchもMySQLもStartできなくなる。
  原因はApatchとMySQLの環境変数が前のディレクトリに残っていたこと。
  以前は「E: ProgramFiles/Xampp/」にインストールしていたが、今回は「E: Xampp」とドライブ直下に。
  これによりサービスの環境変数が前のパスに残ってしまい、新規でインストールしたXammppが不具合を起こしていた模様。
  　※レジストリエディターでAmatch、MySQLそれぞれのサービスパスを設定しなおして解決

  さらに本プロジェクトのデータベースをバックアップし忘れていたため、データベース接続エラー。
  php artisa migrateはおろか、php artisan cache:clearも効かない
  データベースに関わるコマンドが壊れている模様。
  　※以前使っていたlaravel_umarcheテーブルを削除
  　　新規作成し、マイグレートを実施して解決。こういう時にSeederは本当に便利。

　開発中に環境は弄るもんじゃないということを学んだアクシデントだった。

◆解決


◆Error
shop/editでショップ画像が正しくアップロードされず、storage/publicにフォルダが作成されない

1. ざっくり色々確認
  1-1.ストレージリンク

    php artisan storage:linkを実行しリンクを作成
    ERROR  The [E:\xampp\htdocs\laravel\umarche\public\storage] link already exists.
    既にリンクが作成されていた。

  1-2.artisanコマンドでキャッシュクリア
  1-3.windows側のディレクトリプロパティの書き込み権限の確認

2. ヘルパ関数dd()でファイルが正しく送信されているかを確認。
    if (is_null($imageFile)) {
        dd('ファイルが送信されていません');
    }

    if (!$imageFile->isValid()) {
        dd('無効なファイルです');
    }

結果：ddが表示されず、indexへリダイレクト。

3.ファイル情報が正しく送信されているかを確認。

    dd([
        'original_name' => $imageFile->getClientOriginalName(),
        'mime_type' => $imageFile->getMimeType(),
        'size' => $imageFile->getSize(),
    ]);

    Storage::putFile('public/shops', $imageFile);

    return redirect()->route('owner.shops.index');
    }

  ファイル情報は正しく送信されている。

    array:3 [▼ // app\Http\Controllers\Owner\ShopController.php:67
      "original_name" => "christmas-3026688_1920.jpg"
      "mime_type" => "image/jpeg"
      "size" => 1038401
    ]

4. $imageFileプロパティに正しく値が挿入されているかを確認

  コード：

    dd($imageFile);

  結果：

    array:2 [▼ // app\Http\Controllers\Owner\ShopController.php:56
      "_token" => "dDiW1M85y81S2Qnk6SkwOcowtCIFm6f9vxJ2exDx"
      "image" => 
    Illuminate\Http
    \
    UploadedFile
    {#1582 ▼
        -originalName: "christmas-3026688_1920.jpg"
        -mimeType: "image/jpeg"
        -error: 0
        -originalPath: "christmas-3026688_1920.jpg"
        -test: false
        #hashName: null
        path: "E:\xampp\tmp"
        filename: "php10F0.tmp"
        basename: "php10F0.tmp"
        pathname: "E:\xampp\tmp\php10F0.tmp"
        extension: "tmp"
        realPath: "
    E:\xampp
    \
    tmp\php10F0.tmp
    "
        aTime: 2025-04-08 00:01:02
        mTime: 2025-04-08 00:01:02
        cTime: 2025-04-08 00:01:02
        inode: 844424930525407
        size: 1038401
        perms: 0100666
        owner: 0
        group: 0
        type: "file"
        writable: true
        readable: true
        executable: false
        file: true
        dir: false
        link: false
        linkTarget: "E:\xampp\tmp\php10F0.tmp"
      }
    ]

  filename: "php10F0.tmp"とあるので、
  リクエストが正しく送られていることを確認

5. Storage::putFile()メソッドの動作確認。

  public/storageまでファイルパスが通っているか確認。

  コード：

    $imageFile = $request->image;

    if (!is_null($imageFile) && $imageFile->isValid()) {
        // ファイルを保存し、保存先のパスを取得
        $filePath = Storage::putFile('public/shops', $imageFile);
    
        // 保存先のパスを確認
        dd($filePath);
    }

  結果：

    "public/shops/svNVz3JDciLcSkEuqovJUtfBn4JWSEaZ6btEr5Gb.jpg" // app\Http\Controllers\Owner\ShopController.php:62

  ファイルのパスは通っているが、実際にはstorage/publicにshopsディレクトリが作成されていない。

6. 手動でフォルダを生成してみる。
  リクエストもファイルパスも通っているので、ディレクトリだけ自動で生成してみる。

  コード：

    if (!Storage::exists('public/shops')) {
      Storage::makeDirectory('public/shops');
    }

  結果：ディレクトリが自動で作成され、ファイルが保存されるようになったが、なぜか
  storage/app/private/public/shopsに画像が保存される。
  privateフォルダにpublic/shopsディレクトリが階層で自動作成されている模様。

  windowsGUIから手動でpublicフォルダにshopsフォルダを作成したらそっちに今までアップロードした画像が保存されていた。
  コードも問題なく動作する。

7. filesystems.phpを確認。
  色々調べた結果、filesystems.phpのdefault設定がlocalになっていることが原因っぽい。
  Storageモデルのdisk()関数で明示的にディスクを指定

  コード：

     if (!is_null($imageFile) && $imageFile->isValid()) {
        $filePath = Storage::disk('public')->putFile('shops', $imageFile);
     }

    dd($filePath);

  結果：
  dd　"shops/l7exljAbzYzPHb6BIzjnyRdOMvSmGQocmHlIqY0z.jpg" // app\Http\Controllers\Owner\ShopController.php:75
  無事にパスが通り、ディレクトリも作成された。

◆解決！

----------------------------------------------

2025/4/8--------------------------------------
◆Error
InterventionImageを利用し画像のリサイズと圧縮を試みるも上手く導入できず。

メッセージ：Class "Intervention\Image\ImageServiceProvider" not found

よくよく調べてみるとLaravelとInterventionImageのverの問題で導入方法がかなり変わっているよう。

  1.試したこと
    ・Intervention Image のインストール確認（問題なし）
    ・オートローダーを再生成（エラー：Class "Intervention\Image\ImageServiceProvider" not found）
    ・キャッシュのクリア
    ・Intervention Image のクラス名を確認
    ・Composerのバージョンロック
    ・php.iniの設定ファイル書き換え


  結果：
    Laravel10以前はapp.phpでProviderを管理していたが、Laravel11ではServiceProviderはapp/bootstrap/providersに登録されていてapp.phpは触らないように仕様変更された。
    InterventionImage2まではServiceProviderを利用していたが、InterventionImage3になってからはインスタンス化して使うように仕様変更があった。
    ちなみにLaravel11とInterventionImage2に互換性はない。
    あれこれ試して色々調べて、ここまで来るのに5時間ぐらいかかった……。

  2.今回は素直にインスタンス化して使う
    何度も同じコードを書くことになるとプロバイダとして登録する方が望ましいけども、今回はテストだしインスタンス化して使うことに決定。

    ◆Error：GD PHP extension must be installed to use this driver.
      ドライバが正しく読み込まれていない。
      デフォルトでImagickを使う仕様になっているのでGdに書き換え

      参考：https://image.intervention.io/v3/introduction/installation
 
        use Intervention\Image\Drivers\Imagick\Driver;
        ↓
        use Intervention\Image\Drivers\Gd\Driver;


    ◆Error：Call to undefined method Intervention\Image\ImageManager::make()
      Intervention\Image\ImageManagerにmakeメソッドが見つからない。
      画像読み込みはreadメソッドになってるっぽい？
      read()を使用したら正常にリダイレクトするようになった。

      $manager = new ImageManager(new Driver());
      $manager->read($imageFile)->resize(1920, 1080)->encode();

      参考：https://image.intervention.io/v3/basics/instantiation#read-image-sources

        Read Image Sources

        public ImageManager::read(mixed $input, string|array|DecoderInterface $decoders = []): ImageInterface

        With a configured Image Manager it is possible to read images from different sources. The method not only accepts paths from file systems, but also binary image data, Base64-encoded image data or images in Data Uri format. It is also possible to pass a range of objects and PHP resources as input. A complete list can be found below.

    正しくリサイズされてエンコードも出来ている。

◆解決！


◆Error：Shopサムネイルが表示されない。
店舗情報を更新したところ、owner/shops/indexにリダイレクト後に壊れた画像ファイルの表示になりサムネイルが表示されない。

  1.試したこと
    ・データベースに登録があることを確認。
    ・filenameテーブルに自動生成した名前で登録できている。57309063_67f44abb00230.jpg
    ・dd()でShopControllerでfilenameを取得できていることを確認。
      コード：
        dd($shop->filename)
      結果：
        "273467030_67f44c465eafb.jpg" // app\Http\Controllers\Owner\ShopController.php:77

  　となると怪しいのはshop-thumbnailコンポーネントか。上手く変数を渡せていないかも。

  2.shop-thombnailコンポーネントを確認
    shops/index.blade.phpでコンポーネントの変数を確認。
    Blade側で「:属性="変数名"」で指定し、Bladeコンポーネントで「{{ $属性名 }}」で指定する。
    記述の仕方に問題はなさそう。

      Blade.php
        <x-shop-thumbnail :filename="$shop->filename" />
      shop-thumbnail.blade.php
        <img src="{{ asset('storage/shops/' . $filename) }}">

  3.シンボリックリンクの再作成
  storageとpublicのリンクを確認。
    コマンド：php artisan storage:link
    結果：ERROR The [E:\xampp\htdocs\laravel\umarche\public\storage] link already exists.
  
  既にリンクが作成されているらしい。
  publicの状態を確認
    コマンド：dir public

    結果：
        Volume in drive E is ボリューム
        Volume Serial Number is E61D-1EDE
      2025/04/07  20:33    <DIR>          .
      2025/04/07  20:33    <DIR>          ..
      2025/01/24  12:55               740 .htaccess
      2025/04/07  10:11    <DIR>          build
      2025/01/24  12:55                 0 favicon.ico
      2025/04/07  20:33                17 hot
      2025/04/07  21:06    <DIR>          images
      2025/04/06  02:57             1,675 index.php
      2025/01/24  12:55                24 robots.txt
      2025/04/05  17:00    <DIR>          storage
                    5 File(s)          2,456 bytes
                    5 Dir(s)  583,660,560,384 bytes free

  storageが<JUNCTION>になっていないので削除して再作成
    コマンド：
    rmdir public\storage
    dir public

    結果：2025/04/08  07:35    <JUNCTION>     storage [E:\xampp\htdocs\laravel\umarche\storage\app\public]

  シンボリックリンクが正しく通って画像が表示されるようになった。

◆解決！

----------------------------------------------