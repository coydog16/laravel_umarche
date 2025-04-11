<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    {{-- フラッシュメッセージ --}}
                    <x-flash-message status="session('status')" />
                    <div class="mx-auto lg:w-2/3 flex justify-end mb-4  pr-2 md:pr-0 ">
                        <button onclick="location.href='{{ route('owner.products.create') }}'"
                            class="text-white bg-indigo-500 border-0 py-2 md:px-8 focus:outline-none hover:bg-indigo-600 rounded text-lg">新規登録</button>
                    </div>
                    {{-- ショップIDを取得しviewへ渡す --}}
                    <div class="flex flex-wrap">
                        @foreach ($ownerInfo as $owner)
                            @foreach ($owner->shop->product as $product)
                                <div class="w-1/4 p-2 md:p-4">
                                    <a href="{{ route('owner.products.edit', ['product' => $product->id]) }}">
                                        <div class="border rouded-md p-2 md:p-2">
                                            {{-- ショップの画像が設定されているかを判定 --}}
                                            <x-thumbnail filename="{{ $product->imageFirst->filename ?? ''}}" type="products" />
                                            <div class="text-gray-700 my-4">
                                          {{ $product->name }}
                                      </div>
                                        </div>
                                    </a>
                                </div>
                            @endforeach
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
