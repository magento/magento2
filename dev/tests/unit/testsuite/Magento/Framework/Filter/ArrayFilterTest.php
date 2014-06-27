<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Framework\Filter;

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
