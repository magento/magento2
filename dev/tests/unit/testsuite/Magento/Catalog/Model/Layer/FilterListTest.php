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

namespace Magento\Catalog\Model\Layer;

class FilterListTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $attributeListMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $attributeMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $layerMock;

    /**
     * @var \Magento\Catalog\Model\Layer\FilterList
     */
    protected $model;

    protected function setUp()
    {
        $this->objectManagerMock = $this->getMock('\Magento\Framework\ObjectManager');
        $this->attributeListMock = $this->getMock(
            'Magento\Catalog\Model\Layer\Category\FilterableAttributeList', array(), array(), '', false
        );
        $this->attributeMock = $this->getMock(
            '\Magento\Catalog\Model\Resource\Eav\Attribute', array(), array(), '', false
        );
        $filters = array(
            FilterList::CATEGORY_FILTER => 'CategoryFilterClass',
            FilterList::PRICE_FILTER => 'PriceFilterClass',
            FilterList::DECIMAL_FILTER => 'DecimalFilterClass',
            FilterList::ATTRIBUTE_FILTER => 'AttributeFilterClass',

        );
        $this->layerMock = $this->getMock('\Magento\Catalog\Model\Layer', array(), array(), '', false);

        $this->model = new FilterList($this->objectManagerMock, $this->attributeListMock, $filters);
    }

    /**
     * @param string $method
     * @param string $value
     * @param string $expectedClass
     * @dataProvider getFiltersDataProvider
     *
     * @covers \Magento\Catalog\Model\Layer\FilterList::getFilters
     * @covers \Magento\Catalog\Model\Layer\FilterList::createAttributeFilter
     * @covers \Magento\Catalog\Model\Layer\FilterList::__construct
     */
    public function testGetFilters($method, $value, $expectedClass)
    {
        $this->objectManagerMock->expects($this->at(0))
            ->method('create')
            ->will($this->returnValue('filter'));

        $this->objectManagerMock->expects($this->at(1))
            ->method('create')
            ->with($expectedClass, array(
                'data' => array('attribute_model' => $this->attributeMock),
                'layer' => $this->layerMock))
            ->will($this->returnValue('filter'));

        $this->attributeMock->expects($this->once())
            ->method($method)
            ->will($this->returnValue($value));

        $this->attributeListMock->expects($this->once())
            ->method('getList')
            ->will($this->returnValue(array($this->attributeMock)));

        $this->assertEquals(array('filter', 'filter'), $this->model->getFilters($this->layerMock));
    }

    /**
     * @return array
     */
    public function getFiltersDataProvider()
    {
        return array(
            array(
                'method' => 'getAttributeCode',
                'value' => FilterList::PRICE_FILTER,
                'expectedClass' => 'PriceFilterClass',
            ),
            array(
                'method' => 'getBackendType',
                'value' => FilterList::DECIMAL_FILTER,
                'expectedClass' => 'DecimalFilterClass',
            ),
            array(
                'method' => 'getAttributeCode',
                'value' => null,
                'expectedClass' => 'AttributeFilterClass',
            )
        );
    }
}
