<?php

$result = $query->where('active', true)
    ->orderBy('name')
    ->first();

$token = $game->currentPlayer()
    ->firstCard
    ->fresh()
    ->token();

$user = $this->service
    ->find($id)
    ->refresh()
    ->toArray();

$name = $game->player()
    ?->refresh()
    ?->reload()
    ?->getName();

$game = $builder->has(Player::factory()->for($user)->count(3))
    ->create()
    ->createOne();

class Foo
{
    public function bar(): array
    {
        return [
            'director' => Player::factory()
                ->for($this->game)
                ->count(2)
                ->createOne(),
        ];
    }
}

$mapper = (new Mapper($config))
    ->withDefaults()
    ->build()
    ->result();
