<x-app-layout>
    <!-- Product section-->
    <section class="py-5">
        <div class="container px-4 px-lg-5 my-5">
            <div class="row gx-4 gx-lg-5 align-items-center">
                <div class="col-md-6">
                    <img class="card-img-top mb-5 mb-md-0" src="{{ $product->getFirstMediaUrl(conversionName: 'front_large') }}" alt="{{ $product->name }}" />
                </div>
                <div class="col-md-6">
                    <div class="small mb-1">Category: {{ $product->category->name }}</div>
                    <h1 class="display-5 fw-bolder">{{ $product->name }}</h1>
                    <div class="fs-5 mb-5">
                        <!-- Product price-->
                        @if($product->price_before_discount)
                            <span class="text-muted text-decoration-line-through">${{ number_format($product->price_before_discount, 2) }}</span>
                            <span>${{ number_format($product->price, 2) }}</span>
                        @else
                            <span>${{ number_format($product->price, 2) }}</span>
                        @endif
                    </div>
                    <p class="lead">{{ $product->description }}</p>
                    <div class="d-flex">
                        <form action="{{ route('order.store', $product) }}" method="POST">
                            @csrf

                            <button class="btn btn-outline-dark flex-shrink-0" type="submit">
                                <i class="bi-cart-fill me-1"></i>
                                Order Product
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-app-layout>
