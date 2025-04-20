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
use App\Services\ImageService;

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
        $request->validate([
            'name' => 'required|string|max:50',
            'information' => 'required|string|max:1000',
            'is_selling' => 'required',
        ]);

        $imageFile = $request->image;
        // 画像の名前がnullでない、かつisValid()でバリデーションを通過すれば画像を保存
        if (!is_null($imageFile) && $imageFile->isValid()) {
            $fileNameToStore = ImageService::upload($imageFile, 'shops');
        }

        $shop = Shop::findOrFail($id);
        $shop->name = $request->name;
        $shop->information = $request->information;
        $shop->is_selling = $request->is_selling;
        if(!is_null($imageFile) && $imageFile->isValid()){
            $shop->filename = $fileNameToStore;
        }
        // dd($shop->filename);
        $shop->save();


        return redirect()
        ->route('owner.shops.index')
        ->with(['message' => '店舗情報を更新しました。', 'status' => 'info']);
    }
}
