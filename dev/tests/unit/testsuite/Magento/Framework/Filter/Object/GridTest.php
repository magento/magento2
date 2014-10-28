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

namespace Magento\Framework\Filter\Object;

use Magento\Framework\Object;

class GridTest extends \PHPUnit_Framework_TestCase
{
    public function testFilter()
    {
        $entityFactoryMock = $this->getMock(
            'Magento\Framework\Data\Collection\EntityFactoryInterface',
            [],
            [],
            '',
            false
        );
        $entityFactoryMock
            ->expects($this->any())
            ->method('create')
            ->with('Magento\Framework\Object', [])
            ->will(
                $this->returnCallback(
                    function () {
                        return new Object();
                    }
                )
            );

        $gridFilter = new Grid($entityFactoryMock);
        $grid = [
            new Object(['field1' => 'value11', 'field2' => 'value12']),
            new Object(['field3' => 'value23', 'field2' => 'value22']),
        ];

        /** @var \Zend_Filter_Interface $filterMock */
        /** This filter should be applied to all fields values */
        $filterMock = $this->getMock('Zend_Filter_Interface', [], [], '', false);
        $filterMock->expects($this->exactly(4))->method('filter')->will(
            $this->returnCallback(
                function ($input) {
                    return '(' . $input . ')';
                }
            )
        );
        $gridFilter->addFilter($filterMock);

        /** @var \Zend_Filter_Interface $fieldFilterMock */
        /** This filter should be applied to 'field2' field value only */
        $fieldFilterMock = $this->getMock('Zend_Filter_Interface', [], [], '', false);
        $fieldFilterMock->expects($this->exactly(2))->method('filter')->will(
            $this->returnCallback(
                function ($input) {
                    return '[' . $input . ']';
                }
            )
        );
        $gridFilter->addFilter($fieldFilterMock, 'field2');

        /** Execute SUT and ensure that data of grid items was filtered correctly */
        $filteredGrid = $gridFilter->filter($grid);
        $this->assertCount(2, $filteredGrid, 'Quantity of filtered items is invalid.');
        $this->assertEquals(
            ['field1' => '(value11)', 'field2' => '[(value12)]'],
            $filteredGrid[0]->getData(),
            'First grid item was filtered incorrectly.'
        );
        $this->assertEquals(
            ['field3' => '(value23)', 'field2' => '[(value22)]'],
            $filteredGrid[1]->getData(),
            'Second grid item was filtered incorrectly.'
        );
    }
}
