<x-app-layout>
    <x-slot name="header">
        <h1 class="display-4 fw-bolder">About Us</h1>
    </x-slot>

    <div class="lead">
        {{ app(\App\Settings\GeneralSettings::class)->about }}
    </div>
</x-app-layout>
