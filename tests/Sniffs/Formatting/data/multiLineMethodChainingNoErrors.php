<?php

$broken = $query
    ->where('active', true)
    ->first();

$director = Player::factory()
    ->for($game)
    ->createOne([
        'token' => PlayerToken::HAT,
        'turn_order' => 0,
    ]);

$name = $game->player()
    ?->refresh()
    ?->getName();

$user = $this->service
    ->find($id)
    ->toArray();

$token = $game->currentPlayer()
    ->firstCard
    ->token();

$value = $game->firstPlayer->token->value;

$id = $this->game->id;

$path = app()->path();

$player = Player::factory()->createOne();

Route::livewire('/', GameIndex::class)->name('games.index');

$result = $query->where('active', true)->first();

$response = $kernel->handle(
    $request,
)->send()->getContent();
