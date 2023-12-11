<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Product;

use Magento\Catalog\Model\Product\Condition;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

class ConditionTest extends TestCase
{
    /**
     * @var Condition
     */
    private $model;

    public function testApplyToCollection()
    {
        $collection = $this->getMockedAbstractCollection();
        $this->assertInstanceOf(
            Condition::class,
            $this->model->applyToCollection($collection)
        );
    }

    public function testGetIdsSelect()
    {
        $connection = $this->getMockedAdapterInterface();
        $this->assertInstanceOf(Select::class, $this->model->getIdsSelect($connection));
        $this->model->setTable(null);
        $this->assertEmpty($this->model->getIdsSelect($connection));
    }

    protected function setUp(): void
    {
        $helper = new ObjectManager($this);
        $this->model = $helper->getObject(Condition::class);
        $this->model->setTable('testTable')
            ->setPkFieldName('testFieldName');
    }

    /**
     * @return AbstractCollection
     */
    private function getMockedAbstractCollection()
    {
        $mockBuilder = $this->getMockBuilder(AbstractCollection::class)
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

        $mockBuilder = $this->getMockBuilder(AdapterInterface::class)
            ->setMethods(['select'])
            ->disableOriginalConstructor();
        $mock = $mockBuilder->getMockForAbstractClass();

        $mock->expects($this->any())
            ->method('select')
            ->willReturn($mockedDbSelect);

        return $mock;
    }

    /**
     * @return Select
     */
    private function getMockedDbSelect()
    {
        $mockBuilder = $this->getMockBuilder(Select::class)
            ->setMethods(['from'])
            ->disableOriginalConstructor();
        $mock = $mockBuilder->getMockForAbstractClass();

        $mock->expects($this->any())
            ->method('from')
            ->willReturn($mock);

        return $mock;
    }
}
