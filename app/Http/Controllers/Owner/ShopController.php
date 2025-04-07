<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

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

    public function update(Request $request, string $id)
    {

        $imageFile = $request->image;
        // 画像の名前がnullでない、かつisValid()でバリデーションを通過すれば画像を保存
        if (!is_null($imageFile) && $imageFile->isValid()) {
            //storage/app/public/shopsディレクトリへ画像を自動で名前を付けて保存。ディレクトリが無ければ自動生成。
            Storage::disk('public')->putFile('shops', $imageFile);
        }

        return redirect()->route('owner.shops.index');
    }
}
