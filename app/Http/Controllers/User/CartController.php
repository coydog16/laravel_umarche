<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Constants\Common;
use App\Models\Cart;
use App\Models\User;
use App\Models\Stock;
use Illuminate\Support\Facades\Auth; // userIDを取得するためにAuthファサードを使用

class CartController extends Controller
{

    public function index()
    {
        $user = User::findOrFail(Auth::id()); // Auth::id()は、現在認証されているユーザーのIDを取得
        $products = $user->cart; // ユーザーのカート情報を取得
        $totalPrice = 0; // 合計金額を初期化

        foreach ($products as $product) {
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

    public function checkout()
    {

        $user = User::findOrFail(Auth::id()); // Auth::id()は、現在認証されているユーザーのIDを取得
        $products = $user->cart; // ユーザーのカート情報を取得
        // dd($products);

        $lineItems = []; // Stripeへ渡すための配列を初期化
        foreach ($products as $product) { //商品情報を取得して$lineItemsに格納
            $quantity = $product->pivot->quantity; // カートに入っている数量
            $stock = Stock::where('product_id', $product->id)->sum('quantity'); // 商品IDを指定して在庫情報を取得

            if (!$stock || $stock < $quantity) {
                return redirect()->route('user.cart.index')->with('error', '在庫が不足しています。');
            } else {
                $lineItem = [
                    'price_data' => [
                        'currency' => 'jpy', // 通貨を指定
                        'product_data' => [
                            'name' => $product->name,
                            'images' => [$product->imageFirst->filename], // 商品の画像URL
                            'description' => $product->information,
                        ],
                        'unit_amount' => $product->price, // 商品の価格
                    ],
                    'quantity' => $product->pivot->quantity, // カートに入っている数量
                ];

                array_push($lineItems, $lineItem); // $lineItemsに追加
            }
        }
        // dd($lineItems);

        // 在庫情報を更新
        foreach ($products as $product) {
            Stock::create([
                'product_id' => $product->id,
                'type' => Common::PRODUCT_LIST['reduce'],
                'quantity' => $product->pivot->quantity * -1
            ]);
        }

        // dd('test');

        \Stripe\Stripe::setApiKey(env('STRIPE_SECRET_KEY'));

        $session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'line_items' => $lineItems,
            'mode' => 'payment',
            'success_url' => route('cart.success'),
            'cancel_url' => route('cart.cancel'),
        ]);

        $publicKey = env('STRIPE_PUBLIC_KEY');

        return view('user.checkout', compact('session', 'publicKey'));
    }

    public function success()
    {
        Cart::where('user_id', Auth::id())->delete(); // カートの中身を削除

        return redirect()->route('user.items.index');
    }

    public function cancel()
    {
        $user = User::findOrFail(Auth::id()); // Auth::id()は、現在認証されているユーザーのIDを取得
        
        // 在庫情報を更新
        foreach ($user->cart as $product) {
            Stock::create([
                'product_id' => $product->id,
                'type' => Common::PRODUCT_LIST['add'],
                'quantity' => $product->pivot->quantity
            ]);
        }

        return redirect()->route('user.cart.index')->with('error', '決済がキャンセルされました。'); // カートの中身を削除
    }
}
