<?php

use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts::app')] class extends Component
{
    public array $todayStats = [];

    public array $monthStats = [];

    public array $dailySales = [];

    public array $dailyTicks = [];

    public array $monthlySales = [];

    public array $monthlyTicks = [];

    public array $topCustomers = [];

    public array $productSales = [];

    public float $donutCircumference = 0;

    public function mount(): void
    {
        $nbsp = "\u{A0}";

        $this->todayStats = [
            ['icon' => 'beaker', 'label' => 'Total Milk Sold', 'value' => "486{$nbsp}L", 'color' => 'primary'],
            ['icon' => 'banknotes', 'label' => 'Total Cash Received', 'value' => "Rs{$nbsp}42,500", 'color' => 'success'],
            ['icon' => 'credit-card', 'label' => 'Total Credit Sales', 'value' => "Rs{$nbsp}18,200", 'color' => 'secondary'],
            ['icon' => 'clock', 'label' => 'Pending Payments', 'value' => "Rs{$nbsp}9,750", 'color' => 'warning'],
            ['icon' => 'truck', 'label' => 'Deliveries Pending', 'value' => '7', 'color' => 'error'],
        ];

        $this->monthStats = [
            ['icon' => 'trending-up', 'label' => 'Revenue', 'value' => "Rs{$nbsp}1,245,000", 'color' => 'success'],
            ['icon' => 'trending-down', 'label' => 'Expenses', 'value' => "Rs{$nbsp}312,000", 'color' => 'error'],
            ['icon' => 'wallet', 'label' => 'Profit', 'value' => "Rs{$nbsp}933,000", 'color' => 'primary'],
            ['icon' => 'credit-card', 'label' => 'Outstanding Balance', 'value' => "Rs{$nbsp}86,400", 'color' => 'warning'],
        ];

        [$this->dailySales, $this->dailyTicks] = $this->buildBarSeries([
            'Mon' => 38000, 'Tue' => 42500, 'Wed' => 39800, 'Thu' => 45200,
            'Fri' => 51000, 'Sat' => 58400, 'Sun' => 42500,
        ], 10000);

        [$this->monthlySales, $this->monthlyTicks] = $this->buildBarSeries([
            'Feb' => 980000, 'Mar' => 1042000, 'Apr' => 1108000,
            'May' => 995000, 'Jun' => 1176000, 'Jul' => 1245000,
        ], 100000);

        $customers = [
            'Ahmed Traders' => 128500,
            'Green Valley Hotel' => 96200,
            'Al-Noor Bakery' => 84300,
            'Sunrise Cafe' => 71800,
            'City Grocers' => 58900,
        ];
        $maxCustomer = max($customers);
        foreach ($customers as $name => $value) {
            $this->topCustomers[] = [
                'name' => $name,
                'value' => $value,
                'pct' => round($value / $maxCustomer * 100, 1),
            ];
        }

        // Chart-safe darkened variants of the app's primary/secondary/accent/warning
        // tokens — validated as a distinguishable categorical set (see dataviz skill).
        $products = [
            ['name' => 'Milk', 'value' => 52, 'color' => '#422ad5'],
            ['name' => 'Yogurt', 'value' => 24, 'color' => '#b0236d'],
            ['name' => 'Butter', 'value' => 14, 'color' => '#009887'],
            ['name' => 'Cheese', 'value' => 10, 'color' => '#b58400'],
        ];
        $circumference = 2 * M_PI * 70;
        $this->donutCircumference = $circumference;
        $cumulative = 0;
        foreach ($products as $product) {
            $dash = $circumference * $product['value'] / 100;
            $product['dash'] = max($dash - 3, 0);
            $product['offset'] = $cumulative;
            $cumulative += $dash;
            $this->productSales[] = $product;
        }
    }

    private function buildBarSeries(array $raw, int $step): array
    {
        $max = max($raw);
        $niceMax = (int) (ceil($max / $step) * $step);

        $bars = [];
        foreach ($raw as $label => $value) {
            $bars[] = [
                'label' => $label,
                'value' => $value,
                'pct' => round($value / $niceMax * 100, 1),
                'isMax' => $value === $max,
            ];
        }

        return [$bars, [0, (int) ($niceMax / 2), $niceMax]];
    }
};
?>

<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-base-content">Dashboard</h1>
        <p class="mt-1 text-sm text-base-content/60">Super admin overview of today's activity and shop performance.</p>
    </div>

    <div class="mb-8">
        <h2 class="mb-3 text-sm font-semibold text-base-content/70">Today's Summary</h2>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($todayStats as $stat)
                <x-ui.stat-card :icon="$stat['icon']" :value="$stat['value']" :label="$stat['label']" :color="$stat['color']" />
            @endforeach
        </div>
    </div>

    <div class="mb-8">
        <h2 class="mb-3 text-sm font-semibold text-base-content/70">Monthly Summary</h2>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($monthStats as $stat)
                <x-ui.stat-card :icon="$stat['icon']" :value="$stat['value']" :label="$stat['label']" :color="$stat['color']" />
            @endforeach
        </div>
    </div>

    <div>
        <h2 class="mb-3 text-sm font-semibold text-base-content/70">Charts</h2>
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
            <x-ui.card title="Daily Sales" subtitle="Revenue by day, this week (Rs)">
                <x-ui.bar-chart :data="$dailySales" :ticks="$dailyTicks" />
            </x-ui.card>

            <x-ui.card title="Monthly Sales" subtitle="Revenue by month, last 6 months (Rs)">
                <x-ui.bar-chart :data="$monthlySales" :ticks="$monthlyTicks" />
            </x-ui.card>

            <x-ui.card title="Top Customers" subtitle="Highest revenue this month">
                <div class="space-y-3">
                    @foreach ($topCustomers as $customer)
                        <div>
                            <div class="mb-1 flex items-center justify-between gap-2 text-sm">
                                <span class="truncate font-medium text-base-content">{{ $customer['name'] }}</span>
                                <span class="shrink-0 tabular-nums text-base-content/70">Rs {{ number_format($customer['value']) }}</span>
                            </div>
                            <div class="h-2 w-full overflow-hidden rounded-full bg-base-200">
                                <div
                                    class="h-full rounded-full bg-primary"
                                    style="width: {{ $customer['pct'] }}%"
                                    title="{{ $customer['name'] }}: Rs {{ number_format($customer['value']) }}"
                                ></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-ui.card>

            <x-ui.card title="Product Wise Sales" subtitle="Share of revenue by product, this month">
                <div class="flex flex-col items-center gap-4 sm:flex-row sm:justify-center">
                    <svg viewBox="0 0 180 180" width="160" height="160" class="-rotate-90">
                        <circle cx="90" cy="90" r="70" fill="none" stroke="var(--color-base-200)" stroke-width="26" />
                        @foreach ($productSales as $product)
                            <circle
                                cx="90" cy="90" r="70" fill="none"
                                stroke="{{ $product['color'] }}"
                                stroke-width="26"
                                stroke-dasharray="{{ $product['dash'] }} {{ $donutCircumference - $product['dash'] }}"
                                stroke-dashoffset="{{ -$product['offset'] }}"
                                class="transition-opacity hover:opacity-80"
                            ><title>{{ $product['name'] }}: {{ $product['value'] }}%</title></circle>
                        @endforeach
                    </svg>
                    <div class="flex flex-col gap-2">
                        @foreach ($productSales as $product)
                            <div class="flex items-center gap-2 text-sm">
                                <span class="size-2.5 shrink-0 rounded-full" style="background-color: {{ $product['color'] }}"></span>
                                <span class="text-base-content/70">{{ $product['name'] }}</span>
                                <span class="font-semibold text-base-content tabular-nums">{{ $product['value'] }}%</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </x-ui.card>
        </div>
    </div>
</div>
