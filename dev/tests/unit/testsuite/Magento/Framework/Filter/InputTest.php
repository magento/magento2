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

class InputTest extends \PHPUnit_Framework_TestCase
{
    public function testFilterZendFilterAsObject()
    {
        $objectManagerMock = $this->getMock('Magento\Framework\ObjectManager', [], [], '', false);
        $inputFilter = new Input($objectManagerMock);

        /** @var \Zend_Filter_Interface $filterMock */
        /** This filter should be applied to 'field1' field value only */
        $filterMock = $this->getMock('Zend_Filter_Interface', [], [], '', false);
        $filterMock->expects($this->exactly(1))->method('filter')->will(
            $this->returnCallback(
                function ($input) {
                    return '(' . $input . ')';
                }
            )
        );
        $inputFilter->addFilter('field1', $filterMock);

        /** Execute SUT and ensure that array items were filtered correctly */
        $inputArray = ['field1' => 'value1', 'field2' => 'value2'];
        $expectedOutput = ['field1' => '(value1)', 'field2' => 'value2'];
        $this->assertEquals($expectedOutput, $inputFilter->filter($inputArray), 'Array was filtered incorrectly.');
    }

    public function testFilterZendFilterAsArray()
    {
        $objectManagerMock = $this->getMock('Magento\Framework\ObjectManager', [], [], '', false);
        $inputFilter = new Input($objectManagerMock);

        /** This filter should be applied to 'field1' field value only */
        $inputFilter->setFilters(
            array(
                'field1' => array(
                    array(
                        'zend' => 'StringToUpper',
                        'args' => array('encoding' => 'utf-8')
                    )
                )
            )
        );

        /** Execute SUT and ensure that array items were filtered correctly */
        $inputArray = ['field1' => 'value1', 'field2' => 'value2'];
        $expectedOutput = ['field1' => 'VALUE1', 'field2' => 'value2'];
        $this->assertEquals($expectedOutput, $inputFilter->filter($inputArray), 'Array was filtered incorrectly.');
    }
}
