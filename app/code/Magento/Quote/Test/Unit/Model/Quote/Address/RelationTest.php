<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Test\Unit\Model\Quote\Address;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class RelationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Model\AbstractModel | \PHPUnit_Framework_MockObject_MockObject
     */
    private $modelMock;

    /**
     * @var \Magento\Quote\Model\Quote\Address\Relation
     */
    private $relation;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->modelMock = $this->createPartialMock(\Magento\Framework\Model\AbstractModel::class, [
                'getItemsCollection',
                'getShippingRatesCollection',
                'itemsCollectionWasSet',
                'shippingRatesCollectionWasSet'
            ]);
        $this->relation = $objectManager->getObject(\Magento\Quote\Model\Quote\Address\Relation::class, []);
    }

    public function testProcessRelation()
    {
        $itemsCollection = $this->createMock(
            \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection::class
        );
        $shippingRatesCollection = $this->createMock(
            \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection::class
        );
        $this->modelMock->expects($this->once())->method('itemsCollectionWasSet')->willReturn(true);
        $this->modelMock->expects($this->once())->method('getItemsCollection')->willReturn($itemsCollection);
        $this->modelMock->expects($this->once())->method('shippingRatesCollectionWasSet')->willReturn(true);
        $this->modelMock->expects($this->once())
            ->method('getShippingRatesCollection')
            ->willReturn($shippingRatesCollection);
        $itemsCollection->expects($this->once())->method('save');
        $shippingRatesCollection->expects($this->once())->method('save');
        $this->relation->processRelation($this->modelMock);
    }
}
