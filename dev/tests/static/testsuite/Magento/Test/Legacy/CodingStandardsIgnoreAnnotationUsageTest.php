<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Test\Legacy;

use PHPUnit\Framework\TestCase;
use Magento\Framework\App\Utility\Files;

class CodingStandardsIgnoreAnnotationUsageTest extends TestCase
{
    public function testAnnotationUsage()
    {
        $invoker = new \Magento\Framework\App\Utility\AggregateInvoker($this);
        $invoker(
            function ($filename) {
                $fileText = file_get_contents($filename);
                if (strpos($fileText, '@codingStandardsIgnoreFile') !== false) {
                    $this->fail(
                        '@codingStandardsIgnoreFile annotation must be avoided. '
                        . 'Use codingStandardsIgnoreStart/codingStandardsIgnoreEnd to suppress code fragment '
                        . 'or use codingStandardsIgnoreLine to suppress line. '
                        . $filename
                    );
                }
            },
            Files::init()->getPhpFiles(
                Files::INCLUDE_APP_CODE
                | Files::INCLUDE_PUB_CODE
                | Files::INCLUDE_LIBS
                | Files::AS_DATA_SET
                | Files::INCLUDE_NON_CLASSES
            )
        );
    }
}
