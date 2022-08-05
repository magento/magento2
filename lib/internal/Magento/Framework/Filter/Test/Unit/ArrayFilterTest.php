<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Filter\Test\Unit;

use Magento\Framework\Filter\ArrayFilter;
use PHPUnit\Framework\TestCase;

class ArrayFilterTest extends TestCase
{
    public function testFilter()
    {
        $arrayFilter = new ArrayFilter();

        /** @var \Zend_Filter_Interface $filterMock */
        /** This filter should be applied to all fields values */
        $filterMock = $this->createMock(\Zend_Filter_Interface::class);
        $filterMock->expects($this->exactly(3))->method('filter')->willReturnCallback(
            function ($input) {
                return '(' . $input . ')';
            }
        );
        $arrayFilter->addFilter($filterMock);

        /** @var \Zend_Filter_Interface $fieldFilterMock */
        /** This filter should be applied to 'field2' field value only */
        $fieldFilterMock = $this->createMock(\Zend_Filter_Interface::class);
        $fieldFilterMock->expects($this->exactly(1))->method('filter')->willReturnCallback(
            function ($input) {
                return '[' . $input . ']';
            }
        );
        $arrayFilter->addFilter($fieldFilterMock, 'field2');

        /** Execute SUT and ensure that array items were filtered correctly */
        $inputArray = ['field1' => 'value1', 'field2' => 'value2', 'field3' => 'value3'];
        $expectedOutput = ['field1' => '(value1)', 'field2' => '[(value2)]', 'field3' => '(value3)'];
        $this->assertEquals($expectedOutput, $arrayFilter->filter($inputArray), 'Array was filtered incorrectly.');
    }
}
