<?php

namespace Magento\Framework\Webapi\Test\Unit;

use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Reflection\MethodsMap;
use Magento\Framework\Webapi\ServiceOutputProcessor;
use PHPUnit\Framework\TestCase;

class ServiceOutputProcessorTest extends TestCase
{
    /**
     * @dataProvider providerForConvertSimpleValue
     */
    public function testConvertSimpleValue($input, $expected)
    {
        $dataObjectProcessor = $this->createMock(DataObjectProcessor::class);
        $methodsMap = $this->createMock(MethodsMap::class);
        $processor = new ServiceOutputProcessor($dataObjectProcessor, $methodsMap);
        $result = $processor->convertValue($input, '');

        $this->assertEquals($expected, $result);
    }

    public function providerForConvertSimpleValue()
    {
        return [
            'array' => [
                [1,2,3],
                [1,2,3]
            ],
            'associatve array' => [
                [
                    'A' => 'B',
                    'X' => 'Y',
                ],
                [
                    'A' => 'B',
                    'X' => 'Y',
                ],
            ],
            'null' => [
                null,
                [],
            ],
            'string' => [
                'a',
                'a',
            ]
        ];
    }

    public function testConvertObject()
    {
        $dataObjectProcessor = $this->createMock(DataObjectProcessor::class);
        $dataObjectProcessor->expects($this->once())
            ->method('buildOutputDataArray')
            ->with(new \stdClass(), \stdClass::class)
            ->willReturn(['A' => 'B', 'C' => 'D']);
        $methodsMap = $this->createMock(MethodsMap::class);
        $processor = new ServiceOutputProcessor($dataObjectProcessor, $methodsMap);
        $result = $processor->convertValue(new \stdClass(), \stdClass::class);

        $this->assertEquals(['A' => 'B', 'C' => 'D'], $result);
    }
}
