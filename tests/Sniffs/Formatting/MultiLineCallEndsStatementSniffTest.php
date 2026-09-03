<?php

declare(strict_types=1);

namespace Maarheeze\Sniffs\Formatting;

use SlevomatCodingStandard\Sniffs\TestCase;

class MultiLineCallEndsStatementSniffTest extends TestCase
{
    // The closure fixture is checked twice; without this the second check is
    // served the first one's errors from PHP_CodeSniffer's own cache.
    private const array NO_CACHE = ['--no-cache'];

    public function testNoErrors(): void
    {
        $report = self::checkFile(
            __DIR__ . '/data/multiLineCallEndsStatementNoErrors.php',
        );

        self::assertNoSniffErrorInFile($report);
    }

    public function testErrors(): void
    {
        $report = self::checkFile(
            __DIR__ . '/data/multiLineCallEndsStatementErrors.php',
        );

        self::assertSame(6, $report->getErrorCount());

        self::assertSniffError($report, 6, $this->code(), $this->message());
        self::assertSniffError($report, 12, $this->code(), $this->message());
        self::assertSniffError($report, 20, $this->code(), $this->message());
        self::assertSniffError($report, 24, $this->code(), $this->message());
        self::assertSniffError($report, 30, $this->code(), $this->message());
        self::assertSniffError($report, 35, $this->code(), $this->message());
    }

    public function testClosureArgumentsAllowed(): void
    {
        $report = self::checkFile(
            $this->closureArgumentsFile(),
            [],
            [],
            self::NO_CACHE,
        );

        self::assertSame(1, $report->getErrorCount());

        self::assertSniffError($report, 28, $this->code(), $this->message());
    }

    public function testClosureArgumentsDisallowed(): void
    {
        $report = self::checkFile(
            $this->closureArgumentsFile(),
            ['allowClosureArguments' => false],
            [],
            self::NO_CACHE,
        );

        self::assertSame(6, $report->getErrorCount());

        self::assertSniffError($report, 7, $this->code(), $this->message());
        self::assertSniffError($report, 15, $this->code(), $this->message());
        self::assertSniffError($report, 21, $this->code(), $this->message());
        self::assertSniffError($report, 28, $this->code(), $this->message());
        self::assertSniffError($report, 35, $this->code(), $this->message());
        self::assertSniffError($report, 41, $this->code(), $this->message());
    }

    private function closureArgumentsFile(): string
    {
        return __DIR__ . '/data/multiLineCallEndsStatementClosureArguments.php';
    }

    private function code(): string
    {
        return MultiLineCallEndsStatementSniff::CODE_CHAINED_ON_MULTI_LINE_CALL;
    }

    private function message(): string
    {
        return 'must end its statement';
    }
}
