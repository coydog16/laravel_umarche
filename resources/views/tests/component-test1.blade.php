

<x-tests.app>
    <x-slot name="header">ヘッダー１</x-slot>
    コンポーネントテスト１
    <x-tests.card title="タイトル" description="説明" :message="$message" />
    <x-tests.card title="タイトル2" />
    <x-tests.card title="CSSを変更したい" class="bg-red-300"/>
</x-tests.app>
