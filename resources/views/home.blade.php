<x-app-layout>
    <x-slot name="header">
        <h1 class="display-4 fw-bolder">{{ app(\App\Settings\GeneralSettings::class)->title }}</h1>
        <p class="lead fw-normal text-white-50 mb-0">{{ app(\App\Settings\GeneralSettings::class)->subTitle }}</p>
    </x-slot>

    <div class="row gx-4 gx-lg-5 row-cols-2 row-cols-md-3 row-cols-xl-4 justify-content-center">
        @foreach($stockItems as $stockItem)
            <div class="col mb-5">
                <div class="card h-100">
                    @if($stockItem->price_before_discount)
                        <!-- Sale badge-->
                        <div class="badge bg-dark text-white position-absolute" style="top: 0.5rem; right: 0.5rem">
                            Se Vende
                        </div>
                    @endif
                    <!-- stockItem image-->
                    <a href="{{ route('stockItem', $stockItem) }}">
                        <img class="card-img-top" src="{{ $stockItem->getFirstMediaUrl(conversionName: 'thumb') }}" alt="{{ $stockItem->name }}" />
                    </a>
                    <!-- stockItem details-->
                    <div class="card-body p-4">
                        <div class="text-center">
                            <!-- stockItem name-->
                            <h5 class="fw-bolder">
                                <a href="{{ route('stockItem', $stockItem) }}" class="link">{{ $stockItem->name }}</a>
                            </h5>
                            <!-- stockItem price-->
                            @if($stockItem->price_before_discount)
                                <span class="text-muted text-decoration-line-through">${{ $stockItem->price_before_discount }}</span> ${{ number_format($stockItem->price, 2) }}
                            @else
                                ${{ $stockItem->price, 2 }}
                            @endif
                        </div>
                    </div>
                    <!-- stockItem actions-->
                    <div class="card-footer p-4 pt-0 border-top-0 bg-transparent">
                        <div class="text-center"><a class="btn btn-outline-dark mt-auto" href="{{ route('stockItem', $stockItem) }}">Ver Producto</a>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</x-app-layout>
