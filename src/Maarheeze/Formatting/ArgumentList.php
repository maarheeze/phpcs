<?php

declare(strict_types=1);

namespace Maarheeze\Formatting;

use function in_array;

use const T_CLOSE_PARENTHESIS;
use const T_CLOSURE;
use const T_END_HEREDOC;
use const T_END_NOWDOC;
use const T_FN;
use const T_MATCH;
use const T_START_HEREDOC;
use const T_START_NOWDOC;

readonly class ArgumentList
{
    private const array INLINE_FUNCTIONS = [
        T_CLOSURE,
        T_FN,
        T_MATCH,
    ];

    private const array HEREDOC_STARTS = [
        T_START_HEREDOC,
        T_START_NOWDOC,
    ];

    private const array HEREDOC_ENDS = [
        T_END_HEREDOC,
        T_END_NOWDOC,
    ];

    private function __construct(
        private FileTokens $tokens,
        public int $opener,
        public int $closer,
    ) {
    }

    public static function openedAt(FileTokens $tokens, int $opener): self
    {
        return new self(
            $tokens,
            $opener,
            $tokens->pointerAt($opener, 'parenthesis_closer'),
        );
    }

    public static function endingBefore(FileTokens $tokens, int $pointer): ?self
    {
        $previous = $tokens->previousEffectiveBefore($pointer);

        if ($previous === null) {
            return null;
        }

        if ($tokens->codeAt($previous) !== T_CLOSE_PARENTHESIS) {
            return null;
        }

        return new self(
            $tokens,
            $tokens->pointerAt($previous, 'parenthesis_opener'),
            $previous,
        );
    }

    public function spansMultipleLines(): bool
    {
        return $this->tokens->lineAt($this->opener)
            !== $this->tokens->lineAt($this->closer);
    }

    public function breaksOutsideInlineFunctions(): bool
    {
        $pointer = $this->opener;

        while (true) {
            $next = $this->tokens->nextEffectiveAfter($pointer);

            if ($next === null) {
                return false;
            }

            if ($this->tokens->lineAt($next) !== $this->tokens->lineAt($pointer)) {
                return true;
            }

            if ($next === $this->closer) {
                return false;
            }

            $bodyEnd = $this->inlineFunctionBodyEnd($next);

            if ($bodyEnd !== null && $bodyEnd >= $this->closer) {
                return false;
            }

            $pointer = $bodyEnd ?? $next;
        }
    }

    private function inlineFunctionBodyEnd(int $pointer): ?int
    {
        $code = $this->tokens->codeAt($pointer);

        if (in_array($code, self::HEREDOC_STARTS, true)) {
            $end = $this->tokens->file()->findNext(
                self::HEREDOC_ENDS,
                $pointer + 1,
            );

            return $end === false ? null : $end;
        }

        if (in_array($code, self::INLINE_FUNCTIONS, true) === false) {
            return null;
        }

        if ($this->tokens->hasKey($pointer, 'scope_closer') === false) {
            return null;
        }

        return $this->tokens->pointerAt($pointer, 'scope_closer');
    }
}
