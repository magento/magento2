<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Console\Style;

use Symfony\Component\Console\Output\Output;

/**
 * Auxiliary class for MagentoStyleTest.
 */
class TestOutput extends Output
{
    /**
     * Captured output content for testing purposes
     *
     * @var string
     */
    public $output = '';

    public function clear(): void
    {
        $this->output = '';
    }

    /**
     * @param string $message
     * @param bool $newline
     */
    protected function doWrite($message, $newline): void
    {
        $this->output .= $message . ($newline ? "\n" : '');
    }
}
