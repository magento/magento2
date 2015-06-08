<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Test\Unit\Stdlib;

use Magento\Framework\ObjectManager\TMap;

class TArrayTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $testData = [
            0 => new \stdClass(),
            'item' => new \stdClass()
        ];
        $stdClassArray = new TMap('stdClass', $testData);

        foreach ($stdClassArray as $index => $item) {
            static::assertSame($testData[$index], $item);
        }
    }

    public function testFill()
    {
        $testData = [
            0 => new \stdClass(),
            'item' => new \stdClass()
        ];
        $stdClassArray = new TMap('stdClass');
        $stdClassArray[] = $testData[0];
        $stdClassArray['item'] = $testData['item'];

        foreach ($stdClassArray as $index => $item) {
            static::assertSame($testData[$index], $item);
        }
    }

    public function testConstructorException()
    {
        $this->setExpectedException('InvalidArgumentException');

        $testData = [
            0 => new \stdClass(),
            'item' => new \stdClass(),
            'wrong' => ''
        ];

        new TMap('stdClass', $testData);

    }

    public function testFillException()
    {
        $this->setExpectedException('InvalidArgumentException');

        $testData = [
            0 => new \stdClass(),
            'item' => new \stdClass(),
            'wrong' => ''
        ];

        $stdClassArray = new TMap('stdClass');
        $stdClassArray[] = $testData[0];
        $stdClassArray['item'] = $testData['item'];
        $stdClassArray['wrong'] = $testData['wrong'];
    }
}
