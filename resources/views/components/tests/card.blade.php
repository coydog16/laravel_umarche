@props([
    'title' => 'タイトル初期値です。',
    'message' => '初期値です。',
    'description' => '説明初期値です。',
])

<div {{ $attributes->merge([
    'class' =>  'border02 shadow-md w-1/4 p-2'
    ]) }} >
    <div>
        <h2>{{ $title }}</h2>
        <div>画像</div>
        <p>{{ $description }}</p>
        <div>{{ $message }}</div>
    </div>
</div>
