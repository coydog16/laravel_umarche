<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ImageService
{
  public static function upload($imageFile, $folderName){

    $manager = new ImageManager(new Driver()); // ドライバを指定（'gd' または 'imagick'）
    $fileName = uniqid(rand() . '_');// ランダムファイル名を生成
    $extension = $imageFile->extension();// 拡張子を取得
    $fileNameToStore = $fileName . '.' . $extension;// ファイル名と拡張子をつなげる
    $resizedImage = $manager->read($imageFile)->resize(1920, 1080)->encode();// 画像をリサイズ
    Storage::disk('public')->put($folderName . '/' . $fileNameToStore, $resizedImage);// 画像を保存

    return $fileNameToStore;
  }
}