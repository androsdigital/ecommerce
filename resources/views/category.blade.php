<x-app-layout>
    <x-slot name="header">
        <h1 class="display-4 fw-bolder">{{ $category->name }}</h1>
    </x-slot>

    <div class="row gx-4 gx-lg-5 row-cols-2 row-cols-md-3 row-cols-xl-4 justify-content-center">
        @foreach($category->products as $product)
            <div class="col mb-5">
                <div class="card h-100">
                    @if($product->price_before_discount)
                        <!-- Sale badge-->
                        <div class="badge bg-dark text-white position-absolute" style="top: 0.5rem; right: 0.5rem">
                            Sale
                        </div>
                    @endif
                    <!-- Product image-->
                    <a href="{{ route('product', $product) }}">
                        <img class="card-img-top" src="{{ $product->getFirstMediaUrl(conversionName: 'thumb') }}" alt="{{ $product->name }}" />
                    </a>
                    <!-- Product details-->
                    <div class="card-body p-4">
                        <div class="text-center">
                            <!-- Product name-->
                            <h5 class="fw-bolder">
                                <a href="{{ route('product', $product) }}" class="link">{{ $product->name }}</a>
                            </h5>
                            <!-- Product price-->
                            @if($product->price_before_discount)
                                <span class="text-muted text-decoration-line-through">${{ number_format($product->price_before_discount, 2) }}</span> ${{ number_format($product->price, 2) }}
                            @else
                                ${{ number_format($product->price, 2) }}
                            @endif
                        </div>
                    </div>
                    <!-- Product actions-->
                    <div class="card-footer p-4 pt-0 border-top-0 bg-transparent">
                        <div class="text-center"><a class="btn btn-outline-dark mt-auto" href="{{ route('product', $product) }}">View Product</a>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</x-app-layout>
