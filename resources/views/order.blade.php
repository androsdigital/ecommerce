<x-app-layout>
    <x-slot name="header">
        <h1 class="display-4 fw-bolder">Order: {{ $order->id }}</h1>
    </x-slot>

    <div class="alert alert-success" role="alert">
        Successfully ordered
    </div>
</x-app-layout>
