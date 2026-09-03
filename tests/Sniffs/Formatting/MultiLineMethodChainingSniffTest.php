<?php

declare(strict_types=1);

namespace Maarheeze\Sniffs\Formatting;

use SlevomatCodingStandard\Sniffs\TestCase;

class MultiLineMethodChainingSniffTest extends TestCase
{
    public function testNoErrors(): void
    {
        $report = self::checkFile(__DIR__ . '/data/multiLineMethodChainingNoErrors.php');

        self::assertNoSniffErrorInFile($report);
    }

    public function testErrors(): void
    {
        $report = self::checkFile(__DIR__ . '/data/multiLineMethodChainingErrors.php');

        self::assertSame(19, $report->getErrorCount());

        self::assertSniffError($report, 3, $this->code(), 'Call to "orderBy()"');
        self::assertSniffError($report, 3, $this->code(), 'Call to "first()"');
        self::assertSniffError($report, 5, $this->code(), 'Property "firstCard"');
        self::assertSniffError($report, 5, $this->code(), 'Call to "fresh()"');
        self::assertSniffError($report, 5, $this->code(), 'Call to "token()"');
        self::assertSniffError($report, 7, $this->code(), 'Call to "find()"');
        self::assertSniffError($report, 7, $this->code(), 'Call to "refresh()"');
        self::assertSniffError($report, 7, $this->code(), 'Call to "toArray()"');
        self::assertSniffError($report, 9, $this->code(), 'Call to "refresh()"');
        self::assertSniffError($report, 9, $this->code(), 'Call to "reload()"');
        self::assertSniffError($report, 9, $this->code(), 'Call to "getName()"');
        self::assertSniffError($report, 11, $this->code(), 'Call to "create()"');
        self::assertSniffError($report, 11, $this->code(), 'Call to "createOne()"');
        self::assertSniffError($report, 18, $this->code(), 'Call to "for()"');
        self::assertSniffError($report, 18, $this->code(), 'Call to "count()"');
        self::assertSniffError($report, 18, $this->code(), 'Call to "createOne()"');
        self::assertSniffError($report, 23, $this->code(), 'Call to "withDefaults()"');
        self::assertSniffError($report, 23, $this->code(), 'Call to "build()"');
        self::assertSniffError($report, 23, $this->code(), 'Call to "result()"');

        self::assertAllFixedInFile($report);
    }

    public function testMinimumCalls(): void
    {
        $report = self::checkFile(
            __DIR__ . '/data/multiLineMethodChainingMinimumCalls.php',
            ['minimumCalls' => 2],
        );

        self::assertSame(5, $report->getErrorCount());

        self::assertSniffError($report, 5, $this->code(), 'Call to "first()"');
        self::assertSniffError($report, 7, $this->code(), 'Property "firstCard"');
        self::assertSniffError($report, 7, $this->code(), 'Call to "token()"');
        self::assertSniffError($report, 9, $this->code(), 'Call to "find()"');
        self::assertSniffError($report, 9, $this->code(), 'Call to "toArray()"');

        self::assertAllFixedInFile($report);
    }

    private function code(): string
    {
        return MultiLineMethodChainingSniff::CODE_MULTI_LINE_REQUIRED;
    }
}
