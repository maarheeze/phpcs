<?php

Livewire::test(CompanyPanel::class, [
    'game' => $gameScenario->game(),
    'company' => Fake::company(),
])->assertSee('Sell shares');

Livewire::test(CompanyPanel::class, [
    'game' => $gameScenario->game(),
    'company' => Fake::company(),
])
    ->call('listCompany')
    ->assertDispatched('flow-opened', flow: 'list-company');

$rows = $query
    ->where([
        'game_id' => $gameId,
        'active' => true,
    ])
    ->get();

$name = $repository?->find([
    'id' => $id,
])?->name;

$this->assertSame(
    $expected,
    $collection->map([
        'a' => 1,
    ])->all(),
);

$mapper = (new Mapper(
    $config,
))->build();
