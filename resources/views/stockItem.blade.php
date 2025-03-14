<x-app-layout>
    <!-- stockItem section-->
    <section class="py-5">
        <div class="container px-4 px-lg-5 my-5">
            <div class="row gx-4 gx-lg-5 align-items-center">
                <div class="col-md-6">
                    <img class="card-img-top mb-5 mb-md-0" src="{{ $stockItem->getFirstMediaUrl(conversionName: 'front_large') }}" alt="{{ $stockItem->name }}" />
                </div>
                <div class="col-md-6">
                    <div class="small mb-1">Categoría: {{ $stockItem->product->category->name }}</div>
                    <h1 class="display-5 fw-bolder">{{ $stockItem->product->name }}</h1>
                    <div class="fs-5 mb-5">
                        <!-- stockItem price-->
                        @if($stockItem->price_before_discount)
                            <span class="text-muted text-decoration-line-through">${{ $stockItem->price_before_discount }}</span>
                            <span>${{ $stockItem->price }}</span>
                        @else
                            <span>${{ $stockItem->price }}</span>
                        @endif
                    </div>
                    <p class="lead">{{ $stockItem->product->description }}</p>
                    <div class="d-flex">
                        <form action="{{ route('order.store', $stockItem) }}" method="POST">
                            @csrf

                            <button class="btn btn-outline-dark flex-shrink-0" type="submit">
                                <i class="bi-cart-fill me-1"></i>
                                Ordenar Producto
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-app-layout>
