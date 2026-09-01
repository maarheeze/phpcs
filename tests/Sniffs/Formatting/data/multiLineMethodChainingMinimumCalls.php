<?php

$result = $query->where('active', true)->first();

$token = $game->currentPlayer()->firstCard->token();

$director = Player::factory()->for($game)->createOne();

$user = $this->service->find($id)->refresh()->toArray();
