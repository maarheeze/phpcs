<?php

$result = $query->where('active', true)
    ->first();

$token = $game->currentPlayer()
    ->firstCard
    ->token();

$user = $this->service
    ->find($id)
    ->toArray();

$name = $game->player()
    ?->refresh()
    ?->getName();

$game = $builder->has(Player::factory()
    ->for($user)
    ->count(3))
    ->createOne();

class Foo
{
    public function bar(): array
    {
        return [
            'director' => Player::factory()
                ->for($this->game)
                ->createOne(),
        ];
    }
}
