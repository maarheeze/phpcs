<?php

declare(strict_types=1);

namespace Maarheeze\Sniffs\Helpers;

readonly class ChainMember
{
    public function __construct(
        public int $operator,
        public int $name,
        public ?ArgumentList $arguments,
    ) {
    }

    public function isCall(): bool
    {
        return $this->arguments !== null;
    }
}
