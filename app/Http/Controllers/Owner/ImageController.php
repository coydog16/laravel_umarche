<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Image;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\UploadImageRequest;
use App\Services\ImageService;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:owners');

        $this->middleware(function ($request, $next) {

            $id = $request->route()->parameter('image');
            if (!is_null($id)) {
                $imagesOwnerId = Image::findOrFail($id)->owner->id;
                $imageId = (int)$imagesOwnerId;
                if ($imageId !== Auth::id()) {
                    abort(404);
                }
            }
            return $next($request);
        });
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $images = Image::where('owner_id', Auth::id())
            ->orderBy('updated_at', 'desc')
            ->paginate(20);

        return view(('owner.images.index'),
            compact('images')
        );
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('owner.images.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $imageFiles = $request->file('files');
        if (!is_null($imageFiles)) {
            foreach ($imageFiles as $imageFile) {
                $fileNameToStore = ImageService::upload($imageFile, 'products');
                Image::create([
                    'owner_id' => Auth::id(),
                    'filename' => $fileNameToStore
                ]);
            }
        }
        return redirect()
            ->route('owner.images.index')
            ->with(['message' => '画像を登録しました。', 'status' => 'info']);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $image = Image::findOrFail($id);
        return view(('owner.images.edit'),
            compact('image')
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UploadImageRequest $request, string $id)
    {
        $request->validate([
            'title' => 'string|max:50',
        ]);

        $image = Image::findOrFail($id);
        $image->title = $request->title;
        $image->save();

        return redirect()
            ->route('owner.images.index')
            ->with(['message' => '画像情報を更新しました。', 'status' => 'info']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $image = Image::findOrFail($id); // IDを指定して画像を取得

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

        // ストレージ内のファイルを削除
        $filePath = 'products/' . $image->filename;
        if (Storage::disk('public')->exists($filePath)) {
            Storage::disk('public')->delete($filePath);
        }

        // 画像を削除
        $image->delete();

        return redirect()
            ->route('owner.images.index')
            ->with(['message' => '画像を削除しました。', 'status' => 'alert']);
    }
}
