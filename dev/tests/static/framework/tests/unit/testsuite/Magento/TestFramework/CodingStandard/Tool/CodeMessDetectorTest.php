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

        $this->assertEquals(
            class_exists('PHPMD\TextUI\Command'),
            $messDetector->canRun()
        );
    }
}
