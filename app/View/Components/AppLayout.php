<?php

namespace App\View\Components;

use App\Settings\GeneralSettings;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class AppLayout extends Component
{
    public function __construct(public GeneralSettings $settings) {}

    public function render(): View
    {
        return view('layouts.app');
    }
}
