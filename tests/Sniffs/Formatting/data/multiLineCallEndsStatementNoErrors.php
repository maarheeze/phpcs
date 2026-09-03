<?php

Livewire::test(Console::class, ['game' => $game])
    ->dispatch('company-selected', companyNumber: 3)
    ->assertSet('selectedCompany', $company);

$this->firstOrFail(
    static function (MarketRow $marketRow) use ($company): bool {
        return $marketRow->company === $company;
    },
);

$report = $this->service
    ->for($game)
    ->build([
        'from' => $start,
        'to' => $end,
    ]);

$active = $players
    ->filter(static function (Player $player): bool {
        return $player->is_active;
    })
    ->values();

$this->assertEqualsCanonicalizing(
    $expected,
    $actual,
);

$this->assertSame(
    $expected,
    $collection->map($callback)->all(),
);

$mapper = (new Mapper($config))->build();
