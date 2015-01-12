<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Service\V1\Cart;

class TotalsServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $itemTotalsMapperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $totalsMapperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $totalsBuilderMock;

    /**
     * @var TotalsService
     */
    private $service;

    public function setUp()
    {
        $this->quoteMock = $this->getMock(
            'Magento\Sales\Model\Quote', [], [], '', false
        );
        $this->totalsBuilderMock = $this->getMock(
            'Magento\Checkout\Service\V1\Data\Cart\TotalsBuilder',
            ['populateWithArray', 'setItems', 'create'],
            [],
            '',
            false
        );
        $this->totalsMapperMock = $this->getMock(
            'Magento\Checkout\Service\V1\Data\Cart\TotalsMapper', [], [], '', false
        );
        $this->quoteRepositoryMock = $this->getMock(
            'Magento\Sales\Model\QuoteRepository', [], [], '', false
        );
        $this->itemTotalsMapperMock = $this->getMock(
            'Magento\Checkout\Service\V1\Data\Cart\Totals\ItemMapper', ['extractDto'], [], '', false
        );

        $this->service = new TotalsService(
            $this->totalsBuilderMock,
            $this->totalsMapperMock,
            $this->quoteRepositoryMock,
            $this->itemTotalsMapperMock
        );
    }

    public function testGetTotals()
    {
        $cartId = 12;
        $this->quoteRepositoryMock->expects($this->once())->method('getActive')->with($cartId)
            ->will($this->returnValue($this->quoteMock));

        $this->totalsMapperMock->expects($this->once())
            ->method('map')
            ->with($this->quoteMock)
            ->will($this->returnValue(['test']));

        $item = $this->getMock('Magento\Sales\Model\Quote\Item', [], [], '', false);
        $this->quoteMock->expects($this->once())->method('getAllItems')->will($this->returnValue([$item]));
        $this->service->getTotals($cartId);
    }
}
