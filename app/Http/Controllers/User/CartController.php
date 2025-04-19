<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cart;
use Illuminate\Support\Facades\Auth; // userIDを取得するためにAuthファサードを使用

class CartController extends Controller
{
    public function add(Request $request)
    {
        $itemInCart = Cart::where('product_id', $request->product_id) //$request->product_idは、リクエストから取得した商品IDか
            ->where('user_id', Auth::id()) // Auth::id()は、現在認証されているユーザーのIDを取得
            ->first();

        if ($itemInCart) {
            // カートに商品が既に存在する場合、数量を更新
            $itemInCart->quantity += $request->quantity; // リクエストから取得した数量を加算
            $itemInCart->save(); //saveとしないと保存されない
        } else {
            // カートに商品が存在しない場合、新規追加
            Cart::create([
                'product_id' => $request->product_id,
                'user_id' => Auth::id(),
                'quantity' => $request->quantity,
            ]);
        }
        dd('Item added to cart successfully.');
    }
}
