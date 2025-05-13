<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Product\Attribute;

use Magento\Catalog\Model\Product\Attribute\Group;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;
use Magento\Framework\DataObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

class GroupTest extends TestCase
{
    /**
     * @var Group
     */
    private $model;

    public function testHasSystemAttributes()
    {
        $this->model->setId(1);
        $this->assertTrue($this->model->hasSystemAttributes());
    }

    protected function setUp(): void
    {
        $helper = new ObjectManager($this);
        $this->model = $helper->getObject(
            Group::class,
            [
                'attributeCollectionFactory' => $this->getMockedCollectionFactory()
            ]
        );
    }

    /**
     * @return CollectionFactory
     */
    private function getMockedCollectionFactory()
    {
        $mockedCollection = $this->getMockedCollection();

        $mockBuilder = $this->getMockBuilder(
            CollectionFactory::class
        );
        $mock = $mockBuilder->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $mock->expects($this->any())
            ->method('create')
            ->willReturn($mockedCollection);

        return $mock;
    }

    /**
     * @return Collection
     */
    private function getMockedCollection()
    {
        $mockBuilder = $this->getMockBuilder(Collection::class);
        $mock = $mockBuilder->disableOriginalConstructor()
            ->getMock();

        $item = new DataObject();
        $item->setIsUserDefine(false);

        $mock->expects($this->any())
            ->method('setAttributeGroupFilter')
            ->willReturn($mock);
        $mock->expects($this->any())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$item]));

        return $mock;
    }
}
