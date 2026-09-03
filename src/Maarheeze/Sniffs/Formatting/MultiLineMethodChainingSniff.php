<?php

declare(strict_types=1);

namespace Maarheeze\Sniffs\Formatting;

use Maarheeze\Formatting\ArgumentList;
use Maarheeze\Formatting\FileTokens;
use Maarheeze\Formatting\MethodChain;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

use function array_slice;
use function sprintf;
use function str_repeat;

use const T_WHITESPACE;

class MultiLineMethodChainingSniff implements Sniff
{
    public const string CODE_MULTI_LINE_REQUIRED = 'MultiLineRequired';

    /**
     * The number of calls a chain needs before it has to be broken up. A
     * ruleset hands its values over as strings, hence the union type.
     */
    public int|string $minimumCalls = 3;

    private const int INDENTATION_WIDTH = 4;

    public function register(): array
    {
        return MethodChain::OBJECT_OPERATORS;
    }

    public function process(File $phpcsFile, int $stackPtr): void
    {
        $tokens = new FileTokens($phpcsFile);
        $chain = MethodChain::containing($tokens, $stackPtr);

        // Every operator of the chain triggers this sniff; only the first one
        // processes the chain as a whole.
        if ($chain->firstOperator() !== $stackPtr) {
            return;
        }

        // The head is the chain's subject, not a member, so a leading call
        // does not count. Property fetches are members, but they never make a
        // chain worth breaking on their own either.
        if ($chain->callCount() < (int) $this->minimumCalls) {
            return;
        }

        // A chain hanging off a bare variable keeps its first member on the
        // statement's line; one that starts with a call of its own has that
        // call as its head and breaks from the first member onwards.
        $membersToBreak = $chain->startsWithVariable()
            ? array_slice($chain->members(), 1)
            : $chain->members();

        if ($membersToBreak === []) {
            return;
        }

        // A head whose call is broken up already offers its closing bracket to
        // hang the next member from, which beats a line of its own.
        $head = ArgumentList::endingBefore($tokens, $membersToBreak[0]->operator);

        if ($head !== null && $head->spansMultipleLines()) {
            return;
        }

        $indentation = $this->getMemberIndentation($tokens, $chain->start);

        foreach ($membersToBreak as $member) {
            $operator = $member->operator;

            if ($tokens->firstOnLine($operator) === $operator) {
                continue;
            }

            $memberName = $tokens->contentAt($member->name);

            $message = $member->isCall()
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
                if ($tokens->codeAt($i) !== T_WHITESPACE) {
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

    private function getMemberIndentation(FileTokens $tokens, int $chainStart): string
    {
        $column = $tokens->columnAt($tokens->firstOnLine($chainStart));

        return str_repeat(' ', $column - 1 + self::INDENTATION_WIDTH);
    }
}
