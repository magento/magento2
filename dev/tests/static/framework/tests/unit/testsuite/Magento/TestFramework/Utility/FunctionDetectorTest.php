<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\Utility;

class FunctionDetectorTest extends \PHPUnit_Framework_TestCase
{
    public function testDetectFunctions()
    {
        $fixturePath = __DIR__ . '/_files/test.php';
        $expectedResults = [
            31 => ['strtoupper', 'md5'],
            43 => ['security'],
        ];
        $functionDetector = new FunctionDetector();
        $lines = $functionDetector->detectFunctions($fixturePath, ['security', 'md5', 'test', 'strtoupper']);
        self::assertEquals($expectedResults, $lines);
    }
}
