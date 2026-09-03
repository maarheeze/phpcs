<?php

declare(strict_types=1);

namespace Maarheeze\Formatting;

use function array_filter;
use function count;
use function in_array;

use const T_CLOSE_CURLY_BRACKET;
use const T_CLOSE_PARENTHESIS;
use const T_CLOSE_SHORT_ARRAY;
use const T_CLOSE_SQUARE_BRACKET;
use const T_DOUBLE_COLON;
use const T_NEW;
use const T_NS_SEPARATOR;
use const T_NULLSAFE_OBJECT_OPERATOR;
use const T_OBJECT_OPERATOR;
use const T_OPEN_CURLY_BRACKET;
use const T_OPEN_PARENTHESIS;
use const T_OPEN_SHORT_ARRAY;
use const T_OPEN_SQUARE_BRACKET;
use const T_PARENT;
use const T_SELF;
use const T_STATIC;
use const T_STRING;
use const T_VARIABLE;

readonly class MethodChain
{
    public const array OBJECT_OPERATORS = [
        T_OBJECT_OPERATOR,
        T_NULLSAFE_OBJECT_OPERATOR,
    ];

    private const array NAME_TOKENS = [
        T_DOUBLE_COLON,
        T_NEW,
        T_NS_SEPARATOR,
        T_PARENT,
        T_SELF,
        T_STATIC,
        T_STRING,
        T_VARIABLE,
    ];

    /**
     * @param list<ChainMember> $members
     */
    private function __construct(
        private FileTokens $tokens,
        public int $start,
        private array $members,
    ) {
    }

    public static function containing(FileTokens $tokens, int $operator): self
    {
        $start = self::findChainStart($tokens, $operator);

        return new self($tokens, $start, self::findMembers($tokens, $start));
    }

    /**
     * @return list<ChainMember>
     */
    public function members(): array
    {
        return $this->members;
    }

    public function firstOperator(): ?int
    {
        return $this->members[0]->operator ?? null;
    }

    public function callCount(): int
    {
        return count(array_filter(
            $this->members,
            static fn (ChainMember $member): bool => $member->isCall(),
        ));
    }

    public function startsWithVariable(): bool
    {
        return $this->tokens->codeAt($this->start) === T_VARIABLE;
    }

    private static function findChainStart(FileTokens $tokens, int $operator): int
    {
        $pointer = $operator;

        while (true) {
            $previous = $tokens->previousEffectiveBefore($pointer);

            if ($previous === null) {
                return $pointer;
            }

            $code = $tokens->codeAt($previous);

            if ($code === T_CLOSE_PARENTHESIS) {
                $pointer = $tokens->pointerAt($previous, 'parenthesis_opener');

                continue;
            }

            if (self::isBracketCloser($tokens, $previous, $code)) {
                $pointer = $tokens->pointerAt($previous, 'bracket_opener');

                continue;
            }

            if (
                in_array($code, self::NAME_TOKENS, true)
                || in_array($code, self::OBJECT_OPERATORS, true)
            ) {
                $pointer = $previous;

                continue;
            }

            return $pointer;
        }
    }

    /**
     * @return list<ChainMember>
     */
    private static function findMembers(FileTokens $tokens, int $chainStart): array
    {
        $members = [];
        $pointer = self::headEnd($tokens, $chainStart);

        while (true) {
            $pointer = $tokens->nextEffectiveAfter($pointer);

            if ($pointer === null) {
                break;
            }

            $code = $tokens->codeAt($pointer);

            if ($code === T_OPEN_PARENTHESIS) {
                $pointer = $tokens->pointerAt($pointer, 'parenthesis_closer');

                continue;
            }

            if (self::isBracketOpener($tokens, $pointer, $code)) {
                $pointer = $tokens->pointerAt($pointer, 'bracket_closer');

                continue;
            }

            if (in_array($code, self::OBJECT_OPERATORS, true)) {
                $name = $tokens->nextEffectiveAfter($pointer);

                if ($name === null) {
                    break;
                }

                $members[] = new ChainMember(
                    $pointer,
                    $name,
                    self::argumentsAfter($tokens, $name),
                );

                $pointer = $tokens->codeAt($name) === T_OPEN_CURLY_BRACKET
                    ? $tokens->pointerAt($name, 'bracket_closer')
                    : $name;

                continue;
            }

            if (in_array($code, self::NAME_TOKENS, true)) {
                continue;
            }

            break;
        }

        return $members;
    }

    private static function headEnd(FileTokens $tokens, int $chainStart): int
    {
        if ($tokens->codeAt($chainStart) !== T_OPEN_PARENTHESIS) {
            return $chainStart;
        }

        return $tokens->pointerAt($chainStart, 'parenthesis_closer');
    }

    private static function argumentsAfter(FileTokens $tokens, int $name): ?ArgumentList
    {
        $after = $tokens->nextEffectiveAfter($name);

        if ($after === null || $tokens->codeAt($after) !== T_OPEN_PARENTHESIS) {
            return null;
        }

        return ArgumentList::openedAt($tokens, $after);
    }

    private static function isBracketOpener(
        FileTokens $tokens,
        int $pointer,
        int|string $code,
    ): bool {
        if (in_array($code, [T_OPEN_SQUARE_BRACKET, T_OPEN_SHORT_ARRAY], true)) {
            return true;
        }

        return $code === T_OPEN_CURLY_BRACKET
            && $tokens->hasKey($pointer, 'bracket_closer');
    }

    private static function isBracketCloser(
        FileTokens $tokens,
        int $pointer,
        int|string $code,
    ): bool {
        if (in_array($code, [T_CLOSE_SQUARE_BRACKET, T_CLOSE_SHORT_ARRAY], true)) {
            return true;
        }

        return $code === T_CLOSE_CURLY_BRACKET
            && $tokens->hasKey($pointer, 'bracket_opener');
    }
}
