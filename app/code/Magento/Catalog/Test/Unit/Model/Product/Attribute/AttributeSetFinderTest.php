<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Product\Attribute;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Attribute\AttributeSetFinder;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class AttributeSetFinderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Collection|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $productCollection;

    /**
     * @var CollectionFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $productCollectionFactory;

    /**
     * @var AttributeSetFinder
     */
    protected $attributeSetFinder;

    protected function setUp(): void
    {
        $this->productCollection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->productCollectionFactory = $this->getMockBuilder(CollectionFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->productCollectionFactory->expects($this->once())->method('create')->willReturn($this->productCollection);

        $this->attributeSetFinder = (new ObjectManager($this))->getObject(
            AttributeSetFinder::class,
            [
                'productCollectionFactory' => $this->productCollectionFactory,
            ]
        );
    }

    public function testFindAttributeIdsByProductIds()
    {
        $productIds = [1, 2, 3];
        $attributeSetIds = [3, 4, 6];

        $select = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->getMock();
        $select->expects($this->once())->method('reset')->with(Select::COLUMNS)->willReturnSelf();
        $select->expects($this->once())->method('columns')->with(ProductInterface::ATTRIBUTE_SET_ID)->willReturnSelf();
        $select->expects($this->once())->method('where')->with('entity_id IN (?)', $productIds)->willReturnSelf();
        $select->expects($this->once())->method('group')->with(ProductInterface::ATTRIBUTE_SET_ID)->willReturnSelf();

        $connection = $this->getMockForAbstractClass(AdapterInterface::class);
        $connection->expects($this->once())->method('fetchCol')->with($select)->willReturn($attributeSetIds);

        $this->productCollection->expects($this->once())->method('getSelect')->willReturn($select);
        $this->productCollection->expects($this->once())->method('getConnection')->willReturn($connection);

        $this->assertEquals($attributeSetIds, $this->attributeSetFinder->findAttributeSetIdsByProductIds($productIds));
    }
}
