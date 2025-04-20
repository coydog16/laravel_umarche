## Laravel tutorial

## ダウンロード

git clone  
git clone https://github.com/coydog16/laravel_umarche.git  
  
git clone ブランチを指定してダウンロードする場合  
git clone -b ブランチ名 https://github.com/coydog16/laravel_umarche.git  

もしくはzipファイルでダウンロードしてください  

## インストール

cd laravel_umarche  
composer install  
npm install  
npm run dev  

.env.example をコピーして .env ファイルを作成  
  
.envファイルの中の下記のご利用の環境に合わせて変更してください。  
  
```php:.env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_umarche
DB_USERNAME=umarche
DB_PASSWORD=password123
```

XAMPP/MAMPまたは他の開発環境でDBを起動した後  
  
php artisan migrate:fresh --seed  

を実行してデータベーステーブルとダミーデータを追加してください。   

最後に  
php artisan key:generate  
と入力してキーを生成後、  

php artisan serve 
で簡易サーバーを立ち上げて表示を確認してください。  
  
## インストール後の実施事項

画像のダミーデータは  
public/imagesフォルダ内に  
sample1.jpg　～　sample6.jpgとして保存しています。  
  
php artisan storage:linkで  
storageフォルダにリンク後、  
  
storage/app/public/productsフォルダ内に  
保存すると表示されます。  
(productsフォルダがない場合は作成してください。)  
  
ショップの画像も表示する場合は、  
storage/app/public/shopsフォルダを作成し、  
画像を保存してください。  