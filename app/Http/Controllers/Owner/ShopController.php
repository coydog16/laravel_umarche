<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use App\Http\Requests\UploadImageRequest;

class ShopController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:owners');

        $this->middleware(function ($request, $next) {

            $id = $request->route()->parameter('shop');
            if (!is_null($id)) {
                $shopOwnerId = Shop::findOrFail($id)->owner->id;
                $shopId = (int)$shopOwnerId;
                $ownerId = Auth::id();
                if ($shopId !== $ownerId) {
                    abort(404);
                }
            }
            return $next($request);
        });
    }

    public function index()
    {
        // $ownerId = Auth::id();
        $shops = Shop::where('owner_id', Auth::id())->get();

        return view(('owner.shops.index'),
            compact('shops')
        );
    }

    public function edit(string $id)
    {
        $shop = Shop::findOrFail($id);
        // dd(Shop::findOrFail($id));

        return view(('owner.shops.edit'),
            compact('shop')
        );
    }

    public function update(UploadImageRequest $request, string $id)
    {

        $imageFile = $request->image;
        // 画像の名前がnullでない、かつisValid()でバリデーションを通過すれば画像を保存
        if (!is_null($imageFile) && $imageFile->isValid()) {
            // ImageManager を初期化
            $manager = new ImageManager(new Driver()); // ドライバを指定（'gd' または 'imagick'）
            $fileName = uniqid(rand() . '_');// ランダムファイル名を生成
            $extension = $imageFile->extension();// 拡張子を取得
            $fileNameToStore = $fileName . '.' . $extension;// ファイル名と拡張子をつなげる
            $resizedImage = $manager->read($imageFile)->resize(1920, 1080)->encode();// 画像をリサイズ
            //dd($imageFile, $resizedImage);

            Storage::disk('public')->put('shops/' . $fileNameToStore, $resizedImage);// 画像を保存

            // 画像を保存
            //Storage::disk('public')->putFile('shops', $imageFile);
        }

        return redirect()->route('owner.shops.index');
    }
}
