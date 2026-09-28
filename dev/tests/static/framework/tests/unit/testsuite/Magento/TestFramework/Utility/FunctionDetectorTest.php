<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\TestFramework\Utility;

class FunctionDetectorTest extends \PHPUnit\Framework\TestCase
{
    public function testDetectFunctions()
    {
        $fixturePath = __DIR__ . '/_files/test.txt';
        $expectedResults = [
            1 => ['strtoupper', 'strtolower'],
            3 => ['foo'],
            4 => ['foo'],
        ];
        $functionDetector = new FunctionDetector();
        $lines = $functionDetector->detect($fixturePath, ['foo', 'strtoupper', 'test', 'strtolower']);
        $this->assertEquals($expectedResults, $lines);
    }
}
