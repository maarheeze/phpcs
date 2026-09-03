<?php

declare(strict_types=1);

namespace Maarheeze\Formatting;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Util\Tokens;

use function array_key_exists;
use function assert;
use function is_array;
use function is_int;
use function is_string;

use const T_WHITESPACE;

class FileTokens
{
    /** @var array<int, array<string, mixed>> $tokens */
    private array $tokens;

    public function __construct(private readonly File $file)
    {
        $this->tokens = $file->getTokens();
    }

    public function file(): File
    {
        return $this->file;
    }

    public function codeAt(int $pointer): int|string
    {
        $code = $this->tokenAt($pointer)['code'];
        assert(is_int($code) || is_string($code));

        return $code;
    }

    public function contentAt(int $pointer): string
    {
        $content = $this->tokenAt($pointer)['content'];
        assert(is_string($content));

        return $content;
    }

    public function lineAt(int $pointer): int
    {
        $line = $this->tokenAt($pointer)['line'];
        assert(is_int($line));

        return $line;
    }

    public function columnAt(int $pointer): int
    {
        $column = $this->tokenAt($pointer)['column'];
        assert(is_int($column));

        return $column;
    }

    public function hasKey(int $pointer, string $key): bool
    {
        return array_key_exists($key, $this->tokenAt($pointer));
    }

    public function pointerAt(int $pointer, string $key): int
    {
        $target = $this->tokenAt($pointer)[$key];
        assert(is_int($target));

        return $target;
    }

    public function nextEffectiveAfter(int $pointer): ?int
    {
        $next = $this->file->findNext(
            Tokens::EMPTY_TOKENS,
            $pointer + 1,
            null,
            true,
        );

        return $next === false ? null : $next;
    }

    public function previousEffectiveBefore(int $pointer): ?int
    {
        $previous = $this->file->findPrevious(
            Tokens::EMPTY_TOKENS,
            $pointer - 1,
            null,
            true,
        );

        return $previous === false ? null : $previous;
    }

    public function firstOnLine(int $pointer): int
    {
        $line = $this->lineAt($pointer);
        $first = $pointer;

        for ($i = $pointer - 1; $i >= 0; $i--) {
            if ($this->lineAt($i) !== $line) {
                break;
            }

            if ($this->codeAt($i) !== T_WHITESPACE) {
                $first = $i;
            }
        }

        return $first;
    }

    /**
     * @return array<string, mixed>
     */
    private function tokenAt(int $pointer): array
    {
        $token = $this->tokens[$pointer];
        assert(is_array($token));

        return $token;
    }
}
