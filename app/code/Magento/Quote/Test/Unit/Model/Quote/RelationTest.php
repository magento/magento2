<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Model\Quote;

use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Payment;
use Magento\Quote\Model\Quote\Relation;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RelationTest extends TestCase
{
    /**
     * @var Relation
     */
    private $model;

    /**
     * @var Quote|MockObject
     */
    protected $quoteMock;

    /**
     * Mock class dependencies
     */
    protected function setUp(): void
    {
        $this->quoteMock = $this->createMock(Quote::class);

        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(
            Relation::class
        );
    }

    /**
     * Test for processRelation
     */
    public function testProcessRelation()
    {
        $addressCollectionMock = $this->createMock(AbstractCollection::class);
        $this->quoteMock->expects($this->once())->method('addressCollectionWasSet')->willReturn(true);
        $this->quoteMock->expects($this->once())->method('getAddressesCollection')->willReturn($addressCollectionMock);
        $addressCollectionMock->expects($this->once())->method('save');

        $itemsCollectionMock = $this->createMock(AbstractCollection::class);
        $this->quoteMock->expects($this->once())->method('itemsCollectionWasSet')->willReturn(true);
        $this->quoteMock->expects($this->once())->method('getItemsCollection')->willReturn($itemsCollectionMock);
        $itemsCollectionMock->expects($this->once())->method('save');

        $paymentCollectionMock = $this->createMock(AbstractCollection::class);
        $this->quoteMock->expects($this->once())->method('paymentsCollectionWasSet')->willReturn(true);
        $this->quoteMock->expects($this->once())->method('getPaymentsCollection')->willReturn($paymentCollectionMock);
        $paymentCollectionMock->expects($this->once())->method('save');

        $paymentMock = $this->createMock(Payment::class);
        $this->quoteMock->expects($this->once())->method('currentPaymentWasSet')->willReturn(true);
        $this->quoteMock->expects($this->once())->method('getPayment')->willReturn($paymentMock);
        $paymentMock->expects($this->once())->method('save');

        $this->model->processRelation($this->quoteMock);
    }
}
