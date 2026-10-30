<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Console\Test\Unit\Exception;

use Magento\Framework\Console\Exception\GenerationDirectoryAccessException;
use PHPUnit\Framework\TestCase;

class GenerationDirectoryAccessExceptionTest extends TestCase
{
    public function testConstructor()
    {
        $exception = new GenerationDirectoryAccessException();

        $this->assertStringContainsString(
            'Command line user does not have read and write permissions on generated directory.',
            $exception->getMessage()
        );
    }
}
