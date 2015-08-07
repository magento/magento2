<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\CodingStandard\Tool;

class CodeMessDetectorTest extends \PHPUnit_Framework_TestCase
{
    public function testCanRun()
    {
        $messDetector = new \Magento\TestFramework\CodingStandard\Tool\CodeMessDetector(
            'some/ruleset/file.xml',
            'some/report/file.xml'
        );

        /** TODO: Remove provided check after PHPMD will support PHP version 7 */
        $isPhpVersionSupported = version_compare(
            '7.0.0',
            preg_replace('#^([^~+-]+).*$#', '$1', PHP_VERSION),
            '>'
        );

        $this->assertEquals(
            class_exists('PHPMD\TextUI\Command') && $isPhpVersionSupported,
            $messDetector->canRun()
        );
    }
}
