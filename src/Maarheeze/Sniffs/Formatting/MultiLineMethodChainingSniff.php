<?php

declare(strict_types=1);

namespace Maarheeze\Sniffs\Formatting;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;

use function array_filter;
use function array_key_exists;
use function array_slice;
use function assert;
use function count;
use function in_array;
use function is_int;
use function is_string;
use function sprintf;
use function str_repeat;

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
use const T_WHITESPACE;

class MultiLineMethodChainingSniff implements Sniff
{
    public const CODE_MULTI_LINE_REQUIRED = 'MultiLineRequired';

    private const INDENTATION_WIDTH = 4;

    private const MINIMUM_CALLS = 2;

    private const OBJECT_OPERATORS = [
        T_OBJECT_OPERATOR,
        T_NULLSAFE_OBJECT_OPERATOR,
    ];

    private const NAME_TOKENS = [
        T_DOUBLE_COLON,
        T_NEW,
        T_NS_SEPARATOR,
        T_PARENT,
        T_SELF,
        T_STATIC,
        T_STRING,
        T_VARIABLE,
    ];

    private const CALL_END_TOKENS = [
        T_CLOSE_CURLY_BRACKET,
        T_CLOSE_PARENTHESIS,
        T_CLOSE_SQUARE_BRACKET,
        T_PARENT,
        T_SELF,
        T_STATIC,
        T_STRING,
        T_VARIABLE,
    ];

    /**
     * @return list<int|string>
     */
    public function register(): array
    {
        return self::OBJECT_OPERATORS;
    }

    public function process(File $phpcsFile, int $stackPtr): void
    {
        $tokens = $this->tokens($phpcsFile);

        $chainStart = $this->findChainStart($phpcsFile, $stackPtr);
        $members = $this->findMembers($phpcsFile, $chainStart);

        // Every operator of the chain triggers this sniff; only the first one
        // processes the chain as a whole.
        if (count($members) === 0 || $members[0]['operator'] !== $stackPtr) {
            return;
        }

        $calls = count(array_filter(
            $members,
            static fn (array $member): bool => $member['isCall'],
        ));

        if ($this->headIsCall($phpcsFile, $members[0]['operator'])) {
            $calls++;
        }

        // Property fetches are members too, but they never make a chain worth
        // breaking on their own.
        if ($calls < self::MINIMUM_CALLS) {
            return;
        }

        // A chain hanging off a bare variable keeps its first member on the
        // statement's line; one that starts with a call of its own has that
        // call as its head and breaks from the first member onwards.
        $membersToBreak = $tokens[$chainStart]['code'] === T_VARIABLE
            ? array_slice($members, 1)
            : $members;

        $indentation = $this->getMemberIndentation($phpcsFile, $chainStart);

        foreach ($membersToBreak as $member) {
            $operator = $member['operator'];

            if ($this->findFirstOnLine($phpcsFile, $operator) === $operator) {
                continue;
            }

            $memberName = $tokens[$member['name']]['content'];
            assert(is_string($memberName));

            $message = $member['isCall']
                ? sprintf('Call to "%s()" must start a new line.', $memberName)
                : sprintf('Property "%s" must start a new line.', $memberName);

            $fixable = $phpcsFile->addFixableError(
                $message,
                $operator,
                self::CODE_MULTI_LINE_REQUIRED,
            );

            if ($fixable === false) {
                continue;
            }

            $phpcsFile->fixer->beginChangeset();

            for ($i = $operator - 1; $i > 0; $i--) {
                if ($tokens[$i]['code'] !== T_WHITESPACE) {
                    break;
                }

                $phpcsFile->fixer->replaceToken($i, '');
            }

            $phpcsFile->fixer->addContentBefore(
                $operator,
                $phpcsFile->eolChar . $indentation,
            );

            $phpcsFile->fixer->endChangeset();
        }
    }

    /**
     * Walks backwards over the expression the operator is applied to, jumping
     * over bracketed parts. Stops at the enclosing "(", "," or ";", so a chain
     * nested in an argument list is a chain of its own.
     */
    private function findChainStart(File $phpcsFile, int $operator): int
    {
        $tokens = $this->tokens($phpcsFile);
        $pointer = $operator;

        while (true) {
            $previous = $phpcsFile->findPrevious(
                Tokens::EMPTY_TOKENS,
                $pointer - 1,
                null,
                true,
            );

            if ($previous === false) {
                return $pointer;
            }

            $code = $tokens[$previous]['code'];

            if ($code === T_CLOSE_PARENTHESIS) {
                $pointer = $tokens[$previous]['parenthesis_opener'];
                assert(is_int($pointer));

                continue;
            }

            if (
                in_array($code, [T_CLOSE_SQUARE_BRACKET, T_CLOSE_SHORT_ARRAY], true)
                || ($code === T_CLOSE_CURLY_BRACKET
                    && array_key_exists('bracket_opener', $tokens[$previous]))
            ) {
                $pointer = $tokens[$previous]['bracket_opener'];
                assert(is_int($pointer));

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
     * @return list<array{operator: int, name: int, isCall: bool}>
     */
    private function findMembers(File $phpcsFile, int $chainStart): array
    {
        $tokens = $this->tokens($phpcsFile);
        $members = [];
        $pointer = $chainStart;

        while (true) {
            $pointer = $phpcsFile->findNext(
                Tokens::EMPTY_TOKENS,
                $pointer + 1,
                null,
                true,
            );

            if ($pointer === false) {
                break;
            }

            $code = $tokens[$pointer]['code'];

            if ($code === T_OPEN_PARENTHESIS) {
                $pointer = $tokens[$pointer]['parenthesis_closer'];
                assert(is_int($pointer));

                continue;
            }

            if (
                in_array($code, [T_OPEN_SQUARE_BRACKET, T_OPEN_SHORT_ARRAY], true)
                || ($code === T_OPEN_CURLY_BRACKET
                    && array_key_exists('bracket_closer', $tokens[$pointer]))
            ) {
                $pointer = $tokens[$pointer]['bracket_closer'];
                assert(is_int($pointer));

                continue;
            }

            if (in_array($code, self::OBJECT_OPERATORS, true)) {
                $name = $phpcsFile->findNext(
                    Tokens::EMPTY_TOKENS,
                    $pointer + 1,
                    null,
                    true,
                );

                if ($name === false) {
                    break;
                }

                $after = $phpcsFile->findNext(
                    Tokens::EMPTY_TOKENS,
                    $name + 1,
                    null,
                    true,
                );

                $members[] = [
                    'operator' => $pointer,
                    'name' => $name,
                    'isCall' => $after !== false
                        && $tokens[$after]['code'] === T_OPEN_PARENTHESIS,
                ];

                $pointer = $tokens[$name]['code'] === T_OPEN_CURLY_BRACKET
                    ? $tokens[$name]['bracket_closer']
                    : $name;
                assert(is_int($pointer));

                continue;
            }

            if (in_array($code, self::NAME_TOKENS, true)) {
                continue;
            }

            break;
        }

        return $members;
    }

    /**
     * Whether the part before the first operator is a call of its own, as in
     * "Player::factory()" or "new Player()".
     */
    private function headIsCall(File $phpcsFile, int $firstOperator): bool
    {
        $tokens = $this->tokens($phpcsFile);

        $previous = $phpcsFile->findPrevious(
            Tokens::EMPTY_TOKENS,
            $firstOperator - 1,
            null,
            true,
        );

        if ($previous === false || $tokens[$previous]['code'] !== T_CLOSE_PARENTHESIS) {
            return false;
        }

        $opener = $tokens[$previous]['parenthesis_opener'];
        assert(is_int($opener));

        $beforeOpener = $phpcsFile->findPrevious(
            Tokens::EMPTY_TOKENS,
            $opener - 1,
            null,
            true,
        );

        return $beforeOpener !== false
            && in_array($tokens[$beforeOpener]['code'], self::CALL_END_TOKENS, true);
    }

    private function getMemberIndentation(File $phpcsFile, int $chainStart): string
    {
        $tokens = $this->tokens($phpcsFile);
        $firstOnLine = $this->findFirstOnLine($phpcsFile, $chainStart);

        $column = $tokens[$firstOnLine]['column'];
        assert(is_int($column));

        return str_repeat(' ', $column - 1 + self::INDENTATION_WIDTH);
    }

    /**
     * File::getTokens() is documented as a plain "array", which makes every
     * token and every offset of it mixed.
     *
     * @return array<int, array<string, mixed>>
     */
    private function tokens(File $phpcsFile): array
    {
        /** @var array<int, array<string, mixed>> $tokens */
        $tokens = $phpcsFile->getTokens();

        return $tokens;
    }

    private function findFirstOnLine(File $phpcsFile, int $pointer): int
    {
        $tokens = $this->tokens($phpcsFile);
        $line = $tokens[$pointer]['line'];
        $first = $pointer;

        for ($i = $pointer - 1; $i >= 0; $i--) {
            if ($tokens[$i]['line'] !== $line) {
                break;
            }

            if ($tokens[$i]['code'] !== T_WHITESPACE) {
                $first = $i;
            }
        }

        return $first;
    }
}
