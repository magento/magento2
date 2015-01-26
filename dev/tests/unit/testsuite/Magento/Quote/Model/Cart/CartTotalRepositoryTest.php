<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\Cart;

class CartTotalRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Quote\Model\Cart\CartTotalRepository
     */
    protected $model;

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
    private $totalsBuilderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $addressMock;

    public function setUp()
    {
        $this->totalsBuilderMock = $this->getMock(
            'Magento\Quote\Api\Data\TotalsDataBuilder',
            ['populateWithArray', 'setItems', 'create'],
            [],
            '',
            false
        );
        $this->quoteMock = $this->getMock('Magento\Quote\Model\Quote', [], [], '', false);
        $this->quoteRepositoryMock = $this->getMock('Magento\Quote\Model\QuoteRepository', [], [], '', false);
        $this->addressMock = $this->getMock('Magento\Quote\Model\Quote\Address', [], [], '', false);

        $this->model = new CartTotalRepository(
            $this->totalsBuilderMock,
            $this->quoteRepositoryMock
        );
    }

    public function testGetTotals()
    {
        $cartId = 12;
        $this->quoteRepositoryMock->expects($this->once())->method('getActive')->with($cartId)
            ->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->once())->method('getShippingAddress')->willReturn($this->addressMock);
        $this->addressMock->expects($this->once())->method('getData')->willReturn(['addressData']);
        $this->quoteMock->expects($this->once())->method('getData')->willReturn(['quoteData']);

        $item = $this->getMock('Magento\Quote\Model\Quote\Item', [], [], '', false);
        $this->quoteMock->expects($this->once())->method('getAllItems')->will($this->returnValue([$item]));
        $this->model->get($cartId);
    }
}
