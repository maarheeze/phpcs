<?php

declare(strict_types=1);

namespace Maarheeze\Sniffs\Formatting;

use Maarheeze\Formatting\ArgumentList;
use Maarheeze\Formatting\FileTokens;
use Maarheeze\Formatting\MethodChain;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

class MultiLineCallEndsStatementSniff implements Sniff
{
    public const string CODE_CHAINED_ON_MULTI_LINE_CALL = 'ChainedOnMultiLineCall';

    public bool $allowClosureArguments = true;

    private const string MESSAGE = 'A call whose arguments span multiple lines'
        . ' must end its statement; assign it to a variable first.';

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

        foreach ($chain->members() as $member) {
            $precedingCall = ArgumentList::endingBefore($tokens, $member->operator);

            if ($precedingCall === null || $this->mayBeChainedOnto($precedingCall)) {
                continue;
            }

            $phpcsFile->addError(
                self::MESSAGE,
                $member->operator,
                self::CODE_CHAINED_ON_MULTI_LINE_CALL,
            );
        }
    }

    private function mayBeChainedOnto(ArgumentList $call): bool
    {
        if ($call->spansMultipleLines() === false) {
            return true;
        }

        return $this->allowClosureArguments
            && $call->breaksOutsideInlineFunctions() === false;
    }
}
