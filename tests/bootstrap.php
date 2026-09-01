<?php

declare(strict_types=1);

// PHP_CodeSniffer registers its own autoloader instead of exposing its classes
// through Composer's, so loading it here is what makes PHP_CodeSniffer\Runner
// resolvable from the tests.
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../vendor/squizlabs/php_codesniffer/autoload.php';
