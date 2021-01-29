<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Test\Unit\Model\Quote;

use Magento\Quote\Model\Quote\Relation;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class RelationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Relation
     */
    private $model;

    /**
     * @var \Magento\Quote\Model\Quote|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $quoteMock;

    /**
     * Mock class dependencies
     */
    protected function setUp(): void
    {
        $this->quoteMock = $this->createMock(\Magento\Quote\Model\Quote::class);

        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(
            \Magento\Quote\Model\Quote\Relation::class
        );
    }

    /**
     * Test for processRelation
     */
    public function testProcessRelation()
    {
        $addressCollectionMock = $this->createMock(\Magento\Eav\Model\Entity\Collection\AbstractCollection::class);
        $this->quoteMock->expects($this->once())->method('addressCollectionWasSet')->willReturn(true);
        $this->quoteMock->expects($this->once())->method('getAddressesCollection')->willReturn($addressCollectionMock);
        $addressCollectionMock->expects($this->once())->method('save');

        $itemsCollectionMock = $this->createMock(\Magento\Eav\Model\Entity\Collection\AbstractCollection::class);
        $this->quoteMock->expects($this->once())->method('itemsCollectionWasSet')->willReturn(true);
        $this->quoteMock->expects($this->once())->method('getItemsCollection')->willReturn($itemsCollectionMock);
        $itemsCollectionMock->expects($this->once())->method('save');

        $paymentCollectionMock = $this->createMock(\Magento\Eav\Model\Entity\Collection\AbstractCollection::class);
        $this->quoteMock->expects($this->once())->method('paymentsCollectionWasSet')->willReturn(true);
        $this->quoteMock->expects($this->once())->method('getPaymentsCollection')->willReturn($paymentCollectionMock);
        $paymentCollectionMock->expects($this->once())->method('save');

        $paymentMock = $this->createMock(\Magento\Quote\Model\Quote\Payment::class);
        $this->quoteMock->expects($this->once())->method('currentPaymentWasSet')->willReturn(true);
        $this->quoteMock->expects($this->once())->method('getPayment')->willReturn($paymentMock);
        $paymentMock->expects($this->once())->method('save');

        $this->model->processRelation($this->quoteMock);
    }
}
