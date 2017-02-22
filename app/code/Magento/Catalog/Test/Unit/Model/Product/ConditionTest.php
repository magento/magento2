<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Product;

use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class ConditionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Product\Condition
     */
    private $model;

    public function testApplyToCollection()
    {
        $collection = $this->getMockedAbstractCollection();
        $this->assertInstanceOf(
            '\Magento\Catalog\Model\Product\Condition',
            $this->model->applyToCollection($collection)
        );
    }

    public function testGetIdsSelect()
    {
        $connection = $this->getMockedAdapterInterface();
        $this->assertInstanceOf('\Magento\Framework\DB\Select', $this->model->getIdsSelect($connection));
        $this->model->setTable(null);
        $this->assertEmpty($this->model->getIdsSelect($connection));
    }

    protected function setUp()
    {
        $helper = new ObjectManager($this);
        $this->model = $helper->getObject('Magento\Catalog\Model\Product\Condition');
        $this->model->setTable('testTable')
            ->setPkFieldName('testFieldName');
    }

    /**
     * @return AbstractCollection
     */
    private function getMockedAbstractCollection()
    {
        $mockBuilder = $this->getMockBuilder('\Magento\Eav\Model\Entity\Collection\AbstractCollection')
            ->setMethods(['joinTable'])
            ->disableOriginalConstructor();
        $mock = $mockBuilder->getMockForAbstractClass();

        $mock->expects($this->any())
            ->method('joinTable');

        return $mock;
    }

    /**
     * @return AdapterInterface
     */
    private function getMockedAdapterInterface()
    {
        $mockedDbSelect = $this->getMockedDbSelect();

        $mockBuilder = $this->getMockBuilder('\Magento\Framework\DB\Adapter\AdapterInterface')
            ->setMethods(['select'])
            ->disableOriginalConstructor();
        $mock = $mockBuilder->getMockForAbstractClass();

        $mock->expects($this->any())
            ->method('select')
            ->will($this->returnValue($mockedDbSelect));

        return $mock;
    }

    /**
     * @return Select
     */
    private function getMockedDbSelect()
    {
        $mockBuilder = $this->getMockBuilder('\Magento\Framework\DB\Select')
            ->setMethods(['from'])
            ->disableOriginalConstructor();
        $mock = $mockBuilder->getMockForAbstractClass();

        $mock->expects($this->any())
            ->method('from')
            ->will($this->returnValue($mock));

        return $mock;
    }
}
