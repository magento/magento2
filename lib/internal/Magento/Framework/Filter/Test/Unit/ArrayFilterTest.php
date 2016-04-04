<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Filter\Test\Unit;

use \Magento\Framework\Filter\ArrayFilter;

class ArrayFilterTest extends \PHPUnit_Framework_TestCase
{
    public function testFilter()
    {
        $arrayFilter = new ArrayFilter();

        /** @var \Zend_Filter_Interface $filterMock */
        /** This filter should be applied to all fields values */
        $filterMock = $this->getMock('Zend_Filter_Interface', [], [], '', false);
        $filterMock->expects($this->exactly(3))->method('filter')->will(
            $this->returnCallback(
                function ($input) {
                    return '(' . $input . ')';
                }
            )
        );
        $arrayFilter->addFilter($filterMock);

        /** @var \Zend_Filter_Interface $fieldFilterMock */
        /** This filter should be applied to 'field2' field value only */
        $fieldFilterMock = $this->getMock('Zend_Filter_Interface', [], [], '', false);
        $fieldFilterMock->expects($this->exactly(1))->method('filter')->will(
            $this->returnCallback(
                function ($input) {
                    return '[' . $input . ']';
                }
            )
        );
        $arrayFilter->addFilter($fieldFilterMock, 'field2');

        /** Execute SUT and ensure that array items were filtered correctly */
        $inputArray = ['field1' => 'value1', 'field2' => 'value2', 'field3' => 'value3'];
        $expectedOutput = ['field1' => '(value1)', 'field2' => '[(value2)]', 'field3' => '(value3)'];
        $this->assertEquals($expectedOutput, $arrayFilter->filter($inputArray), 'Array was filtered incorrectly.');
    }
}
