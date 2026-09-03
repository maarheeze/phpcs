<?php

$active = $players
    ->filter(static function (Player $player): bool {
        return $player->is_active;
    })
    ->values();

$labels = $rows
    ->map(static fn (Row $row): string => sprintf(
        '%s (%s)',
        $row->label,
        $row->suffix,
    ))
    ->all();

$sent = $mailer
    ->send(<<<TEXT
        Dear player, the game has started.
        TEXT)
    ->count();

$found = $query
    ->where(
        'active',
        true,
    )
    ->get();

$label = $game
    ->status(match ($state) {
        State::OPEN => 'open',
        State::CLOSED => 'closed',
    })
    ->label();

$plain = $mailer
    ->sendRaw(<<<'TEXT'
        The game has started.
        TEXT)
    ->count();
