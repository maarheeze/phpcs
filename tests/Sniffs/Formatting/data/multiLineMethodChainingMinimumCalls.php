<?php

$player = Player::factory()->createOne();

$result = $query->where('active', true)->first();

$token = $game->currentPlayer()->firstCard->token();

$user = $this->service->find($id)->toArray();
