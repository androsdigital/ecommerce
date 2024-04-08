<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $ordersToday = Order::whereDate('created_at', today())->count();
        $ordersYesterday = Order::whereDate('created_at', today()->subDay())->count();

        if ($ordersYesterday > 0) {
            $differenceInPercentage = round(($ordersToday - $ordersYesterday) * 100 / $ordersYesterday, 2);
        } else {
            $differenceInPercentage = 0;
        }

        $icon = $differenceInPercentage > 0 ? 'trending-up' : 'trending-down';
        $description = $differenceInPercentage > 0 ? 'increase' : 'decrease';
        $color = $differenceInPercentage > 0 ? 'success' : 'danger';

        return [
            Stat::make('New orders today', $ordersToday)
                ->description($differenceInPercentage . '% ' . $description . ' since yeasterday')
                ->descriptionIcon('heroicon-m-arrow-' . $icon)
                ->color($color),
        ];
    }
}
