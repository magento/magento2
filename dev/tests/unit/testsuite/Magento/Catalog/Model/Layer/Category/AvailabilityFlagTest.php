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

namespace Magento\Catalog\Model\Layer\Category;

class AvailabilityFlagTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    protected $filters;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $filterMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $layerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $stateMock;

    /**
     * @var \Magento\Catalog\Model\Layer\Category\AvailabilityFlag
     */
    protected $model;

    protected function setUp()
    {
        $this->filterMock = $this->getMock(
            '\Magento\Catalog\Model\Layer\Filter\AbstractFilter', array(), array(), '', false
        );
        $this->filters = array($this->filterMock);
        $this->layerMock = $this->getMock('\Magento\Catalog\Model\Layer', array(), array(), '', false);
        $this->stateMock = $this->getMock('\Magento\Catalog\Model\Layer\State', array(), array(), '', false);
        $this->model = new AvailabilityFlag();
    }

    /**
     * @param int $itemsCount
     * @param array $filters
     * @param bool $expectedResult
     *
     * @dataProvider isEnabledDataProvider
     * @covers \Magento\Catalog\Model\Layer\Category\AvailabilityFlag::isEnabled
     * @covers \Magento\Catalog\Model\Layer\Category\AvailabilityFlag::canShowOptions
     */
    public function testIsEnabled($itemsCount, $filters, $expectedResult)
    {
        $this->layerMock->expects($this->any())->method('getState')->will($this->returnValue($this->stateMock));
        $this->stateMock->expects($this->any())->method('getFilters')->will($this->returnValue($filters));
        $this->filterMock->expects($this->once())->method('getItemsCount')->will($this->returnValue($itemsCount));

        $this->assertEquals($expectedResult, $this->model->isEnabled($this->layerMock, $this->filters));
    }

    /**
     * @return array
     */
    public function isEnabledDataProvider()
    {
        return array(
            array(
                'itemsCount' => 0,
                'filters' => array(),
                'expectedResult' => false,
            ),
            array(
                'itemsCount' => 0,
                'filters' => array('filter'),
                'expectedResult' => true,
            ),
            array(
                'itemsCount' => 1,
                'filters' => 0,
                'expectedResult' => true,
            ),
            array(
                'itemsCount' => 1,
                'filters' => array('filter'),
                'expectedResult' => true,
            )
        );
    }
}
