<x-app-layout>
    <x-slot name="header">
        <h1 class="display-4 fw-bolder">Orden: {{ $order->id }}</h1>
    </x-slot>

    <div class="alert alert-success" role="alert">
        Orden creada con éxito.
    </div>
</x-app-layout>
