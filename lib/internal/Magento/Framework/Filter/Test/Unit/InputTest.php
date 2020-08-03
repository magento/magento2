<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Filter\Test\Unit;

use Magento\Framework\Filter\Input;
use Magento\Framework\ObjectManagerInterface;
use PHPUnit\Framework\TestCase;

class InputTest extends TestCase
{
    public function testFilterZendFilterAsObject()
    {
        $objectManagerMock = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $inputFilter = new Input($objectManagerMock);

        /** @var \Zend_Filter_Interface $filterMock */
        /** This filter should be applied to 'field1' field value only */
        $filterMock = $this->createMock(\Zend_Filter_Interface::class);
        $filterMock->expects($this->exactly(1))->method('filter')->willReturnCallback(
            function ($input) {
                return '(' . $input . ')';
            }
        );
        $inputFilter->addFilter('field1', $filterMock);

        /** Execute SUT and ensure that array items were filtered correctly */
        $inputArray = ['field1' => 'value1', 'field2' => 'value2'];
        $expectedOutput = ['field1' => '(value1)', 'field2' => 'value2'];
        $this->assertEquals($expectedOutput, $inputFilter->filter($inputArray), 'Array was filtered incorrectly.');
    }

    public function testFilterZendFilterAsArray()
    {
        $objectManagerMock = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $inputFilter = new Input($objectManagerMock);

        /** This filter should be applied to 'field1' field value only */
        $inputFilter->setFilters(
            [
                'field1' => [
                    [
                        'zend' => 'StringToUpper',
                        'args' => ['encoding' => 'utf-8'],
                    ],
                ],
            ]
        );

        /** Execute SUT and ensure that array items were filtered correctly */
        $inputArray = ['field1' => 'value1', 'field2' => 'value2'];
        $expectedOutput = ['field1' => 'VALUE1', 'field2' => 'value2'];
        $this->assertEquals($expectedOutput, $inputFilter->filter($inputArray), 'Array was filtered incorrectly.');
    }
}
