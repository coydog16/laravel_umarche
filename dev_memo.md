2025/4/5--------------------------------------
### Error
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

### Error
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
 

### Error
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

### Error
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

### Error
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

### Error
Adminでログアウトすると404エラー
admin.phpのadmin.welcomeをコメントアウトしたため、ログアウト後のリダイレクト先がNotFound。

1.destroyメソッドのリダイレクト先を編集（解決）

  app/Http/Controllers/Admin/Auth/AuthenticatedSessionController.php内のdestroyメソッドにおけるリダイレクト先を管理者(admin)のログイン画面に。

### Accident
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

### Error
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
## Error
InterventionImageを利用し画像のリサイズと圧縮を試みるも上手く導入できず。

メッセージ：Class "Intervention\Image\ImageServiceProvider" not found

よくよく調べてみるとLaravelとInterventionImageのverの問題で導入方法がかなり変わっているよう。

### 試したこと
-Intervention Image のインストール確認（問題なし）
-オートローダーを再生成（エラー：Class "Intervention\Image\ImageServiceProvider" not found）
-キャッシュのクリア
-Intervention Image のクラス名を確認
-Composerのバージョンロック
-php.iniの設定ファイル書き換え

Laravel10以前はapp.phpでProviderを管理していたが、Laravel11ではServiceProviderはapp/bootstrap/providersに登録されていてapp.phpは触らないように仕様変更された。
InterventionImage2まではServiceProviderを利用していたが、InterventionImage3になってからはインスタンス化して使うように仕様変更があった。
ちなみにLaravel11とInterventionImage2に互換性はない。
あれこれ試して色々調べて、ここまで来るのに5時間ぐらいかかった……。

### 今回は素直にインスタンス化して使う
何度も同じコードを書くことになるとプロバイダとして登録する方が望ましいけども、今回はテストだしインスタンス化して使うことに決定。

#### Error：GD PHP extension must be installed to use this driver.
ドライバが正しく読み込まれていない。
デフォルトでImagickを使う仕様になっているのでGdに書き換え

参考：[InterventionImage3: 導入](https://image.intervention.io/v3/introduction/installation)

 `use Intervention\Image\Drivers\Imagick\Driver;`

↓
 `use Intervention\Image\Drivers\Gd\Driver;`

#### Error：Call to undefined method Intervention\Image\ImageManager::make()
Intervention\Image\ImageManagerにmakeメソッドが見つからない。
画像読み込みはreadメソッドになってるっぽい？
read()を使用したら正常にリダイレクトするようになった。

```php: Intervention3
$manager = new ImageManager(new Driver()); 
$manager->read($imageFile)->resize(1920, 1080)->encode(); 

```

参考：[Intervention3#read-image-sources](https://image.intervention.io/v3/basics/instantiation#read-image-sources)

>Read Image Sources
public ImageManager::read(mixed $input, string|array|DecoderInterface $decoders = []): ImageInterface
With a configured Image Manager it is possible to read images from different sources. The method not only accepts paths from file systems, but also binary image data, Base64-encoded image data or images in Data Uri format. It is also possible to pass a range of objects and PHP resources as input. A complete list can be found below.

正しくリサイズされてエンコードも出来ている。

◆解決！

## Error：Shopサムネイルが表示されない。
店舗情報を更新したところ、owner/shops/indexにリダイレクト後に壊れた画像ファイルの表示になりサムネイルが表示されない。

### 試したこと

-データベースに登録があることを確認。
-filenameテーブルに自動生成した名前で登録できている。57309063_67f44abb00230.jpg
-`dd($shop->filename)`でfilename(273467030_67f44c465eafb.jpg)を取得できていることを確認。

#### shop-thombnailコンポーネントを確認

shops/index.blade.phpでコンポーネントの変数を確認。
Blade側で「:属性="変数名"」で指定し、Bladeコンポーネントで「{{ $属性名 }}」で指定する。
記述の仕方に問題はなさそう。

Blade.php
<x-shop-thumbnail :filename="$shop->filename" />
shop-thumbnail.blade.php
<img src="{{ asset('storage/shops/' . $filename) }}">

#### シンボリックリンクの再作成

storageとpublicのリンクを確認。

コマンド：php artisan storage:link
結果：ERROR The [E:\xampp\htdocs\laravel\umarche\public\storage] link already exists.

既にリンクが作成されているらしい。
publicの状態を確認

```console:dir public
dir public

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
```

                    

storageが<JUNCTION>になっていないので削除して再作成

```console:rmdir public\storage

dir public
2025/04/08  07:35    <JUNCTION>     storage [E:\xampp\htdocs\laravel\umarche\storage\app\public]

```

シンボリックリンクが正しく通って画像が表示されるようになった。

◆解決！

----------------------------------------------
2025/4/10-------------------------------------

## 画像を登録した際、縦横比を16：9のアスペクト比でトリミングとリサイズを行う仕様を追加。

◆Accident：縦長の画像をリサイズすると画像の縦横比が変わって伸びてしまう

1.resize()ではなく、cover()を使う。
  色々調べていると、やはりIntervention Image3.0からmakeメソッドは廃止されているよう。
  公式ドキュメントを参照し、Cover()を利用するようにする。
  リサイズとトリミングを同時に行ってくれる便利なメソッド。

  参照：https://image.intervention.io/v3/modifying/resizing

  Example Code
  
```php:Intervention Image3.0
    use Intervention\Image\ImageManager;
    use Intervention\Image\Drivers\Gd\Driver;

    // create new image instance (800 x 600)
    $manager = new ImageManager(Driver::class);
    $image = $manager->read('images/example.jpg');

    // crop the best fitting 5:3 (600x360) ratio and resize to 600x360 pixel
    $img->cover(600, 360);

    // crop the best fitting 1:1 ratio (200x200) and resize to 200x200 pixel
    $img->cover(200, 200);

    // cover a size of 300x300 and position crop on the left
    $image->cover(300, 300, 'left'); // 300 x 300 px
```

## 商品を登録するときに画像を登録したものから選択するモーダルウィンドを追加

◆Error：micromodalでファイル選択が想定した動作をしない
画像は4枚まで選べるようにしたが、4番目のボタンを選択して画像を登録すると3番目のサムネイルに上書きされる。
どうやら1～3番目のボタンを選び画像を登録した後に4番目から選ぶとダメなようで
４番目から選ぶと正しく動作する。

1. 検証でimgファイルのデータを見てみる
  画像の1～3を選択した後に4を選択すると、　data_id がimage3-4となっていて
  data-modal はmodal-4が選択されるはずが modal-3となっており、image3の設定を引き継いでいるよう。

  色々調べたら同じ症状になっている人がいるようで、1～3のモーダルウィンドウをMircoModal.close(modal); で閉じているのが原因みたい。
  確かに上記をコメントアウトしてjavascript側に画像クリックで閉じる動きを追加したら症状がなくなった。

  モーダルウィンドウに `<div data-micromodal-trigger=""></div>` を追加しても想定通りに動作したということで、
  ライブラリ側の問題のよう。

  今回はdivタグを追加する方向で対応。

2025/4/11-------------------------------------

## Productを新規登録する機能を追加。
-MVCの構築
-プロダクトテーブル
-プロダクトのダミーデータを生成

----------------------------------------------

2025/4/12-------------------------------------

## Productの内容を編集する機能を追加。

product/editのviewファイルを作成。

データベースの在庫情報を参照し、更新時に購入等でデータベース内の変更があった際に
更新ボタンを押しても更新を行わず、ルート情報を保持したままEdit画面へリダイレクトする仕様を追加。

```php: ProductController/updateメソッド

public function update(ProductRequest $request, string $id)
{

    $request->validate([
        'current_quantity' => 'required|integer',
    ]);

    $product = Product::findOrFail($id);

    $quantity = Stock::where('product_id', $product->id)
        ->sum('quantity');

    if ($request->current_quantity !== $quantity) {
        return redirect()
            ->route('owner.products.edit', ['product' => $id])
            ->with([
                'message' => '在庫数が変更されています。再度確認してください。',
                'status' => 'alert',
            ]);
    } else {
        dd($request->all());
    }

}

```

## 追加のチェックボックスに－の値を入れた際にバリデーションがかかる機能を追加

ProdcutControllerのupdateメソッドに以下のコードを記述

```php:ProdcutController/updateメソッド
$product = Product::findOrFail($id); 

$quantity = Stock::where('product_id', $product->id)
    ->sum('quantity');

if ($request->current_quantity !== $quantity) {
    return redirect()
        ->route('owner.products.edit', ['product' => $id])
        ->with([
            'message' => '在庫数が変更されています。再度確認してください。',
            'status' => 'alert',
        ]);
} else {
    try {
        DB::Transaction(function () use ($request, $product) {

            $product->name = $request->name;
            $product->information = $request->information;
            $product->price = $request->price;
            $product->sort_order = $request->sort_order;
            $product->shop_id = $request->shop_id;
            $product->secondary_category_id = $request->category;
            $product->image1 = $request->image1;
            $product->image2 = $request->image2;
            $product->image3 = $request->image3;
            $product->image4 = $request->image4;
            $product->is_selling = $request->is_selling;
            $product->save();
            
            if($request->type === '1'){
                $newQuantity = $request->quantity;
            }
            if($request->type === '2'){
                $newQuantity = $request->quantity * -1;
            }

            Stock::create([
                'product_id' => $product->id,
                'type' => $request->type,
                'quantity' => $newQuantity
            ]);
        });
    } catch (Throwable $e) {
        Log::error($e);
        throw $e;
    }

    return redirect()
        ->route('owner.products.index')
        ->with(['message' => '商品情報を更新しました。', 'status' => 'info']);
}
```

### Error：バリデーションのエラーメッセージが表示されない

最初はフラッシュメッセージを疑い、コントローラーを疑い、色々試したがコンポーネントを利用していることを思いだす

```php:edit.brade.php
<x-input-error :messages="$errors->get('name')" class="mt-2" />

```

```php:input-error.blade.php
@props(['messages'])

@if ($messages)
    <ul {{ $attributes->merge(['class' => 'text-sm text-red-600 dark:text-red-400 space-y-1']) }}>
        @foreach ((array) $messages as $message)
            <li>{{ $message }}</li>
        @endforeach
    </ul>
@endif
```

構文に問題はないが、 `get()` に属性を渡すのを忘れていた。
各inputタグで設定した属性を渡して解決。

----------------------------------------------

2025/4/13-------------------------------------

## マジックナンバー回避

`ProductController/update()` でtypeをそれぞれ暫定的に1と2で設定していたが、マジックナンバーとなってしまう。
※マジックナンバー：何を意味しているのか分からない数字
  今回の例ではそれぞれ追加と削減の意味を持つ数字だが、何をしているのかよく分からない。

```php:マジックナンバーの例
if($request->type === '1'){
  $newQuantity = $request->quantity; 
}
if($request->type === '2'){
  $newQuantity = $request->quantity * -1; 
}

```

この1と2の数字を`app/Constants/common.php`に定数クラスを作成して分かりやすくする。

```php:app/Constants/common.php
namespace App\Constants;

class Common
{
  const PRODUCT_ADD = "1";
  const PRODUCT_REDUCE = "2";

  const PRODUCT_LIST = [
    'add' => self::PRODUCT_ADD,
    'reduce' => self::PRODUCT_REDUCE,];
}
```

`app/config/app.php` にエイリアス設定を追記すればバックスラッシュで使えるようになる

```php: 使用例
\Constant:: PRODUCT_LIST['add']; 
\Constant:: PRODUCT_LIST['reduce']; 

```

...はずだが、Laravel11からエイリアスの設定が`config/app.app`から別の場所に行っているようで
調べるのに時間がかかりそうなので今回はuse文で読み込むことにする。

これでそれぞれの定数が何をしているかが分かりやすくなった。
```php:ProductController
use App\Constants\Common;

if ($request->type === Common::PRODUCT_LIST['add']) {
    $newQuantity = $request->quantity;
}
if ($request->type === Common::PRODUCT_LIST['reduce']) {
    $newQuantity = $request->quantity * -1;
}
```

## ProdcutDelete

コントローラーにdelet処理を記述
```php: ProductController
public function destroy(string $id)
{

    Product::findOrFail($id)->delete();

    return redirect()
        ->route('owner.products.index')
        ->with(['message' => '商品を削除しました。', 'status' => 'alert']);

}

```

本当に削除するのかを確認するポップアップ
```javascript:view/owner/product/edit/blade.php
function deletePost(e) {
    'use strict';
    if (confirm('本当に削除してもいいですか?')) {
        document.getElementById('delete_' + e.dataset.id).submit();
    }
}
```

```php:view/owner/product/edit/blade.php
<form id="delete_{{ $product->id }}" method="post"
action="{{ route('owner.products.destroy', ['product' => $product->id]) }}">

    @csrf
    @method('delete')
    <div class="p-2 w-full flex justify-around mt-24">
        <a href="#" data-id="{{ $product->id }}" onclick="deletePost(this)"
            class="text-white bg-red-400 border-0 py-2 px-4 focus:outline-none hover:bg-red-500 rounded text-lg ">削除する</a>
      </div>

</form>

```

### 画像を削除する際にプロダクトに紐づいていると外部キー制約で削除できない

画像を使っているか確認して、使ってる場合はproductのimage1～4をnullに変更して削除する。

```php:ImageController.php
// 画像IDを持つ商品を取得
$imageInProducts = Product::where('image1', $image->id)
    ->orWhere('image2', $image->id)
    ->orWhere('image3', $image->id)
    ->orWhere('image4', $image->id)
    ->get();

// 商品の画像参照を解除
if ($imageInProducts->isNotEmpty()) {
    $imageInProducts->each(function ($product) use ($image) {
        if ($product->image1 === $image->id) {
            $product->image1 = null;
        }
        if ($product->image2 === $image->id) {
            $product->image2 = null;
        }
        if ($product->image3 === $image->id) {
            $product->image3 = null;
        }
        if ($product->image4 === $image->id) {
            $product->image4 = null;
        }
        $product->save();
    });
}
```

### Routoの調整

`route\owner.php` のwilcomeページへのRouteと `route\ownerAuth.php` 新規登録（Resister）は今のところ使う予定がないのでコメントアウト
ログアウト後のリダイレクト先が `return redirect('/owner');` とwelcomeページになっているため、 `return redirect('/owner/login');` としてログインページに変更

```php: AuthenticatedSessionController.php

public function destroy(Request $request): RedirectResponse
{

    Auth::guard('owners')->logout();

    $request->session()->invalidate();

    $request->session()->regenerateToken();

    return redirect('/owner/login');

}

```

##Userの実装準備

adminとownerのコントローラーをそれぞれAdmin/Auth、Owner/Authで独立して作成したので
分かりやすいように既存のAuthフォルダのファイルをUser/Authの階層に移動。
各認証系ファイルの名前空間を`use App\Http\Controllers\User\Auth`に修正。

伴い、Routoのファイル名を`userAuth.php`に、app.phpとwep.phpの`require __DIR__.'/auth.php';`を`require __DIR__.'/userAuth.php';`に修正。

Migrationファイルはデフォルトであるのでそれを流用。SeederファイルがないのでUserSeederを新たに作成。

```console
php artisan make:seed UserSeeder
```

DB FacadesとHash Facadesを貼り付け、内容はAdminのデータを流用。
```php: UserSeeder
use Illuminate\Support\Facades\DB; 
use Illuminate\Support\Facades\Hash; 

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            'name' => 'test',
            'email' => 'test@test.com',
            'password' => Hash::make('password123'),
            'created_at' => '2023/08/25 22:45:40'
        ]);
    }

}
```

`DatabaseSeeder.php`に`UserSeeder::class,`を追記。

`php artisan migrate:fresh --seed`と`php artisan migrate:refresh --seed`を実行しMigrateを確認。
[ユーザー用のログインページ](http://127.0.0.1:8000/login)でログインできるか確認。

デフォルトの`nagication.blade.php`のファイルネームを`user-nagication.blade.php`に変更。

ロゴのサイズが大きいのでtailwindCSSで`w-12`を指定して修正。

```php:user-navigation.blade.php

<div class="w-12">
    <a href="{{ route('dashboard') }}">
        <x-application-logo class="block h-9 w-auto fill-current text-gray-800 dark:text-gray-200" />
    </a>
</div>

```

## 商品一覧の実装準備

### ルート情報を設定

```php:web.php
use App\Http\Controllers\User\ItemController;

Route::middleware('auth:users')->group(function(){
        Route::get('/', [ItemController::class,'index'])->name('user.items.index');
   
    });
```
dashboardは利用しないのでwep.phpのdashboardのRouto情報はすべてコメントアウト。

### ItemController作成

`php artisan make:controller user/ItemController`でItemControllerを作成。
まずはそのままviewを返す。

```php:ItemController

class ItemController extends Controller
{
    public function index()
    {
        return view('user.index');
    }
}

```

`resource/views`にuserフォルダを作成し、`index.blade.php`を作成し「商品一覧」とだけ書いておく。
[ローカルホスト](http://127.0.0.1:8000/)にログインし、商品一覧の画面が最初に表示されることを確認。

### 商品一覧のview側の雛形を作成

user/index.blade.phpの中身を一旦テストとしてowner側のproduct/indexの中身をforeachで表示することに。

```php:user/index.blade.php
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="flex flex-wrap">
                        @foreach ($products as $product)
                            <div class="w-1/4 p-2 md:p-4">
                                <a href=""> //ルート情報は一旦削除
                                    <div class="border rouded-md p-2 md:p-2">
                                        {{-- ショップの画像が設定されているかを判定 --}}
                                        <x-thumbnail filename="{{ $product->imageFirst->filename ?? '' }}"
                                            type="products" />
                                        <div class="text-gray-700 my-4">
                                            {{ $product->name }}
                                        </div>
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

```

ItemControllerでProductの情報を取得

```php:User/ItemContller
use App\Models\Product;

class ItemController extends Controller
{
    public function index()
    {
        $products = Product::all();
        return view('user.index', compact('products'));
    }
}
```

`user-navigation.blade.php`のdashboradへのRouto情報を全て`user.items.index`に修正。
[ローカルホスト](http://127.0.0.1:8000/)にログインし、エラーが発生していないことを確認。


### Faker＆Factoryで大量のダミーデータ作成

```Composer.json:Fakerのver確認
"fakerphp/faker": "^1.23",
```

Fakerを日本語化
```php:config/app.php

'faker_locale' => env('APP_FAKER_LOCALE', 'ja_JP'),

```

consoleコマンドでFactoryファイルを作成
`php artisan make:factory ProductFactory --model=Product`
`php artisan make:factory StockFactory --model=Stock`

使い方は[Fakerチートシート](https://qiita.com/tosite0345/items/1d47961947a6770053af)を参照

作成した2つのFactoryファイルにそれぞれ値を入力

```php:ProductFactory.php

public function definition(): array
{
    return [
        'name' => fake()->name(),
        'introduction' => fake()->realText(200, 2),
        'price' => fake()->numberBetween(10, 100000),
        'is_selling' => fake()->numberBetween(0, 1),
        'sort_order' => fake()->randomNumber(),
        'shop_id' => fake()->numberBetween(1, 2),
        'secondary_category_id' => fake()->numberBetween(1, 2),
        'image1' => fake()->numberBetween(1, 6),
        'image2' => fake()->numberBetween(1, 6),
        'image3' => fake()->numberBetween(1, 6),
        'image4' => fake()->numberBetween(1, 6),
    ];
}

```

```php:StockFactory.php

use App\Models\Product;　//use文でProductモデルを読み込み、外部キーを紐づけ

public function definition(): array
{
    return [
        'product_id' => Product::factory(),
        'type' => fake()->numberBetween(1, 2),
        'quantity' => fake()->numberBetween(1, 100),
    ];
}

```

Seederを呼び出すためにデータベースseederにコードを追記
※外部キー制約を設定しているテーブルを先に作成する

```php:Datacase.seeder

use App\Models\Product;
use App\Models\Stock;

Product::factory(100)->create();
Stock::factory(100)->create();

```

`php artisan migrate:refresh --seed`でSeederデータが正常に作成されるか確認。


#### Error：`HasFactory`が見つからないエラー。

`Trait "App\Models\HasFactory" not found`

Laravel11ではデフォルトで`use HasFactpory`の記述がないため、追記する必要があるらしい。

`Models/Stock.php`と`Models/Product.php`に下記を追記

```php
use Illuminate\Database\Eloquent\Factories\HasFactory;
use HasFactory
```


#### Error：`introduction`というフィールド名がデータベース上に見つからないエラー。

```console

SQLSTATE[42S22]: Column not found: 1054 Unknown column 'introduction' in 'field list' (Connection: mysql, SQL: insert into products (name, introduction, price, is_selling, sort_order, shop_id, secondary_category_id, image1, image2, image3, image4, updated_at, created_at) values (加藤 里佳, るというこのごろに来ているように赤旗あ かりこっちをふる朝にもつれてみように苹果りんどんです」「あ、お母っかさね直なおにそこらえているために、早く見ながれました。「ぼくたちにとなの幸さいわの窓まどの人の人はわかった人の人が、そこにこの水ぎわに沿そっちへ来て、なんだ」「みんながらんな立派りっぱりその牛乳屋ぎゅうに、わずカムパネルラのうして見ます。こいです。その苹果りんごの肉にくりました。その見ると、。, 88660, 0, 25494, 1, 1, 1, 2, 4, 2, 2025-04-13 16:17:55, 2025-04-13 16:17:55))

```

`introduction`というフィールド名がデータベース上に見つからないエラー。
`ProducFactory.php`のデータの一部の記載ミスだったので該当箇所を`information`に修正。

再度`php artisan migrate:refresh --seed`でSeederデータが正常に作成されるか確認。
phpMyAdminでproductテーブルとt_stockテーブルそれぞれにダミーデータが生成されているか確認。


#### Error：ユーザーのログイン時に404NotFound

以前[ルート情報を設定](###ルート情報を設定)の際にdashboardをコメントアウトした影響。
User/Authフォルダ内の全てのControllerのリダイレクト先を`dashboard`から`user.items.index`に修正。


## UserIndexのview調整

tailblockからCSSを拝借し、index.blade.phpで商品の情報を追加して調整

```php:user/index.blade.php
<div class="mt-4">
  <h3 class="text-gray-500 text-xs tracking-widest title-font mb-1">{{ $product->category->name }}</h3>
  <h2 class="text-gray-900 title-font text-lg font-medium">{{ $product->name }}</h2>
  <p class="mt-1">${{ number_format($product->price) }}<span class="text-sm text-gray-700">円（税込）</span></p>
</div>
```

----------------------------------------------
