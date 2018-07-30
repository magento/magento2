<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Console\Style;

use Symfony\Component\Console\Output\Output;

/**
 * Auxiliary class for MagentoStyleTest.
 */
class TestOutput extends Output
{
    public $output = '';

    public function clear()
    {
        $this->output = '';
    }

    /**
     * @param string $message
     * @param bool $newline
     */
    protected function doWrite($message, $newline)
    {
        $this->output .= $message . ($newline ? "\n" : '');
    }
}
