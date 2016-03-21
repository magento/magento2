<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Tests to ensure that all license blocks are represented by placeholders
 */
namespace Magento\Test\Legacy;

class LicenseTest extends \PHPUnit_Framework_TestCase
{
    public function testLegacyComment()
    {
        $invoker = new \Magento\Framework\App\Utility\AggregateInvoker($this);
        $invoker(
            function ($filename) {
                $fileText = file_get_contents($filename);
                if (!preg_match_all('#/\*\*.+@copyright.+?\*/#s', $fileText, $matches)) {
                    return;
                }

                foreach ($matches[0] as $commentText) {
                    foreach (['Irubin Consulting Inc', 'DBA Varien', 'Magento Inc'] as $legacyText) {
                        $this->assertNotContains(
                            $legacyText,
                            $commentText,
                            "The license of file {$filename} contains legacy text."
                        );
                    }
                }
            },
            $this->legacyCommentDataProvider()
        );
    }

    public function legacyCommentDataProvider()
    {
        $allFiles = \Magento\Framework\App\Utility\Files::init()->getAllFiles();
        $result = [];
        foreach ($allFiles as $file) {
            if (!file_exists($file[0]) || !is_readable($file[0])) {
                continue;
            }
            $result[] = [$file[0]];
        }
        return $result;
    }
}
