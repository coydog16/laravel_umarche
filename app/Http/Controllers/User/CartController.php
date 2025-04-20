<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\User;
use Illuminate\Support\Facades\Auth; // userIDを取得するためにAuthファサードを使用

class CartController extends Controller
{

    public function index()
    {
        $user = User::findOrFail(Auth::id()); // Auth::id()は、現在認証されているユーザーのIDを取得
        $products = $user->cart; // ユーザーのカート情報を取得
        $totalPrice = 0; // 合計金額を初期化

        foreach($products as $product) {
            $totalPrice += $product->price * $product->pivot->quantity; // 商品の価格と数量を掛け算して合計金額を計算
        }

        return view('user.cart', compact('products', 'totalPrice')); // カート情報をビューに渡す
    }

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
        
        return redirect()->route('user.cart.index');
    }

    public function delete($id)
    {
        Cart::where('product_id', $id) // リクエストから取得した商品ID
            ->where('user_id', Auth::id()) // Auth::id()は、現在認証されているユーザーのIDを取得
            ->delete();

        return redirect()->route('user.cart.index');
    }
}
