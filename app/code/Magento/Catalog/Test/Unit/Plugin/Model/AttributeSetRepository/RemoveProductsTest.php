<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Plugin\Model\AttributeSetRepository;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Plugin\Model\AttributeSetRepository\RemoveProducts;
use Magento\Eav\Api\AttributeSetRepositoryInterface;
use Magento\Eav\Api\Data\AttributeSetInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Provide tests for RemoveProducts plugin.
 */
class RemoveProductsTest extends TestCase
{
    /**
     * @var RemoveProducts
     */
    private $testSubject;

    /**
     * @var CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $collectionFactory;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->collectionFactory = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->testSubject = $objectManager->getObject(
            RemoveProducts::class,
            [
                'collectionFactory' => $this->collectionFactory,
            ]
        );
    }

    /**
     * Test plugin will delete all related products for given attribute set.
     */
    public function testAfterDelete()
    {
        $attributeSetId = '1';

        /** @var Collection|\PHPUnit_Framework_MockObject_MockObject $collection */
        $collection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $collection->expects($this->once())
            ->method('addFieldToFilter')
            ->with($this->identicalTo('attribute_set_id'), $this->identicalTo(['eq' => $attributeSetId]));
        $collection->expects($this->once())
            ->method('delete');

        $this->collectionFactory->expects($this->once())
            ->method('create')
            ->willReturn($collection);

        /** @var AttributeSetRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject $attributeSetRepository */
        $attributeSetRepository = $this->getMockBuilder(AttributeSetRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        /** @var AttributeSetInterface|\PHPUnit_Framework_MockObject_MockObject $attributeSet */
        $attributeSet = $this->getMockBuilder(AttributeSetInterface::class)
            ->setMethods(['getId'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $attributeSet->expects($this->once())
            ->method('getId')
            ->willReturn($attributeSetId);

        $this->assertTrue($this->testSubject->afterDelete($attributeSetRepository, true, $attributeSet));
    }
}
