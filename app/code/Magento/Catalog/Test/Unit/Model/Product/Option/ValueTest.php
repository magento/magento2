<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Product\Option;

use \Magento\Catalog\Model\Product\Option\Value;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Option;
use Magento\Framework\Model\ActionValidator\RemoveAction;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class ValueTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Product\Option\Value
     */
    private $model;

    public function testSaveProduct()
    {
        $this->model->setValues([100])
            ->setData('option_type_id', -1)
            ->setDataChanges(false)
            ->isDeleted(false);
        $this->assertInstanceOf('\Magento\Catalog\Model\Product\Option\Value', $this->model->saveValues());

        $this->model->setData('is_delete', 1)
            ->setData('option_type_id', 1)
            ->setValues([100]);
        $this->assertInstanceOf('\Magento\Catalog\Model\Product\Option\Value', $this->model->saveValues());
    }

    public function testGetPrice()
    {
        $this->model->setPrice(1000);
        $this->model->setPriceType(Value::TYPE_PERCENT);
        $this->assertEquals(1000, $this->model->getPrice(false));

        $this->assertEquals(100, $this->model->getPrice(true));
    }

    public function testGetValuesCollection()
    {
        $this->assertInstanceOf(
            '\Magento\Catalog\Model\ResourceModel\Product\Option\Value\Collection',
            $this->model->getValuesCollection($this->getMockedOption())
        );
    }

    public function testGetValuesByOption()
    {
        $this->assertInstanceOf(
            '\Magento\Catalog\Model\ResourceModel\Product\Option\Value\Collection',
            $this->model->getValuesByOption([1], 1, 1)
        );
    }

    public function testGetProduct()
    {
        $this->assertInstanceOf('\Magento\Catalog\Model\Product', $this->model->getProduct());
    }

    public function testDuplicate()
    {
        $this->assertInstanceOf('\Magento\Catalog\Model\Product\Option\Value', $this->model->duplicate(1, 1));
    }

    public function testDeleteValues()
    {
        $this->assertInstanceOf('\Magento\Catalog\Model\Product\Option\Value', $this->model->deleteValues(1));
    }

    public function testDeleteValue()
    {
        $this->assertInstanceOf('\Magento\Catalog\Model\Product\Option\Value', $this->model->deleteValue(1));
    }

    protected function setUp()
    {
        $mockedResource = $this->getMockedResource();
        $mockedCollectionFactory = $this->getMockedValueCollectionFactory();
        $mockedContext = $this->getMockedContext();
        $helper = new ObjectManager($this);
        $this->model = $helper->getObject(
            '\Magento\Catalog\Model\Product\Option\Value',
            [
                'resource' => $mockedResource,
                'valueCollectionFactory' => $mockedCollectionFactory,
                'context' => $mockedContext
            ]
        );
        $this->model->setOption($this->getMockedOption());
    }

    /**
     * @return \Magento\Catalog\Model\ResourceModel\Product\Option\Value\CollectionFactory
     */
    private function getMockedValueCollectionFactory()
    {
        $mockedCollection = $this->getMockedValueCollection();

        $mockBuilder =
            $this->getMockBuilder('\Magento\Catalog\Model\ResourceModel\Product\Option\Value\CollectionFactory')
            ->setMethods(['create'])
            ->disableOriginalConstructor();
        $mock = $mockBuilder->getMock();

        $mock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($mockedCollection));

        return $mock;
    }

    /**
     * @return \Magento\Catalog\Model\ResourceModel\Product\Option\Value\Collection
     */
    private function getMockedValueCollection()
    {
        $mockBuilder = $this->getMockBuilder('\Magento\Catalog\Model\ResourceModel\Product\Option\Value\Collection')
            ->setMethods(['addFieldToFilter', 'getValuesByOption', 'getValues'])
            ->disableOriginalConstructor();
        $mock = $mockBuilder->getMock();

        $mock->expects($this->any())
            ->method('addFieldToFilter')
            ->will($this->returnValue($mock));

        $mock->expects($this->any())
            ->method('getValuesByOption')
            ->will($this->returnValue($mock));

        $mock->expects($this->any())
            ->method('getValues')
            ->will($this->returnValue($mock));

        return $mock;
    }

    /**
     * @return Option
     */
    private function getMockedOption()
    {
        $mockedProduct = $this->getMockedProduct();

        $mockBuilder = $this->getMockBuilder('\Magento\Catalog\Model\Product\Option')
            ->disableOriginalConstructor();
        $mock = $mockBuilder->getMock();

        $mock->expects($this->any())
            ->method('getProduct')
            ->will($this->returnValue($mockedProduct));

        return $mock;
    }

    /**
     * @return Product
     */
    private function getMockedProduct()
    {
        $mockBuilder = $this->getMockBuilder('\Magento\Catalog\Model\Product')
            ->setMethods(['getFinalPrice', '__wakeup'])
            ->disableOriginalConstructor();
        $mock = $mockBuilder->getMock();

        $mock->expects($this->any())
            ->method('getFinalPrice')
            ->will($this->returnValue(10));

        return $mock;
    }

    /**
     * @return \Magento\Catalog\Model\ResourceModel\Product\Option\Value
     */
    private function getMockedResource()
    {
        $mockBuilder = $this->getMockBuilder('\Magento\Catalog\Model\ResourceModel\Product\Option\Value')
            ->setMethods(
                [
                    'duplicate',
                    '__wakeup',
                    'getIdFieldName',
                    'deleteValues',
                    'deleteValue',
                    'beginTransaction',
                    'delete',
                    'commit',
                    'save',
                    'addCommitCallback',
                ]
            )
            ->disableOriginalConstructor();
        $mock = $mockBuilder->getMock();

        $mock->expects($this->any())
            ->method('duplicate');

        $mock->expects($this->any())
            ->method('deleteValues');

        $mock->expects($this->any())
            ->method('deleteValue');

        $mock->expects($this->any())
            ->method('delete');

        $mock->expects($this->any())
            ->method('save');

        $mock->expects($this->any())
            ->method('commit');

        $mock->expects($this->any())
            ->method('addCommitCallback')
            ->will($this->returnValue($mock));

        $mock->expects($this->any())
            ->method('beginTransaction');

        $mock->expects($this->any())
            ->method('getIdFieldName')
            ->will($this->returnValue('testField'));

        return $mock;
    }

    /**
     * @return \Magento\Framework\Model\Context
     */
    private function getMockedContext()
    {
        $mockedRemoveAction = $this->getMockedRemoveAction();
        $mockEventManager = $this->getMockedEventManager();

        $mockBuilder = $this->getMockBuilder('\Magento\Framework\Model\Context')
            ->setMethods(['getActionValidator', 'getEventDispatcher'])
            ->disableOriginalConstructor();
        $mock = $mockBuilder->getMock();

        $mock->expects($this->any())
            ->method('getActionValidator')
            ->will($this->returnValue($mockedRemoveAction));

        $mock->expects($this->any())
            ->method('getEventDispatcher')
            ->will($this->returnValue($mockEventManager));

        return $mock;
    }

    /**
     * @return RemoveAction
     */
    private function getMockedRemoveAction()
    {
        $mockBuilder = $this->getMockBuilder('\Magento\Framework\Model\Context')
            ->setMethods(['isAllowed'])
            ->disableOriginalConstructor();
        $mock = $mockBuilder->getMock();

        $mock->expects($this->any())
            ->method('isAllowed')
            ->will($this->returnValue(true));

        return $mock;
    }

    /**
     * @return \Magento\Framework\Event\ManagerInterface
     */
    private function getMockedEventManager()
    {
        $mockBuilder = $this->getMockBuilder('\Magento\Framework\Event\ManagerInterface')
            ->setMethods(['dispatch'])
            ->disableOriginalConstructor();
        $mock = $mockBuilder->getMockForAbstractClass();

        $mock->expects($this->any())
            ->method('dispatch');

        return $mock;
    }
}
