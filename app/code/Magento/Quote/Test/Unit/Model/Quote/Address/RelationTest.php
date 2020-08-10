<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Model\Quote\Address;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Model\Quote\Address\Relation;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RelationTest extends TestCase
{
    /**
     * @var AbstractModel|MockObject
     */
    private $modelMock;

    /**
     * @var Relation
     */
    private $relation;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->modelMock = $this->getMockBuilder(AbstractModel::class)
            ->addMethods(
                [
                    'getItemsCollection',
                    'getShippingRatesCollection',
                    'itemsCollectionWasSet',
                    'shippingRatesCollectionWasSet'
                ]
            )
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->relation = $objectManager->getObject(Relation::class, []);
    }

    public function testProcessRelation()
    {
        $itemsCollection = $this->createMock(
            AbstractCollection::class
        );
        $shippingRatesCollection = $this->createMock(
            AbstractCollection::class
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
