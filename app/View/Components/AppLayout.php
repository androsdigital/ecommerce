<?php

namespace App\View\Components;

use Illuminate\View\Component;
use App\Settings\GeneralSettings;
use Illuminate\Contracts\View\View;

class AppLayout extends Component
{
    public function __construct(public GeneralSettings $settings) {}

    public function render(): View
    {
        return view('layouts.app');
    }
}
