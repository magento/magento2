<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Test\Unit\Model\Cart;

use \Magento\Quote\Model\Cart\CartTotalRepository;

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
    private $totalsFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $addressMock;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataObjectHelperMock;

    public function setUp()
    {
        $this->totalsFactoryMock = $this->getMock(
            'Magento\Quote\Api\Data\TotalsInterfaceFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->quoteMock = $this->getMock('Magento\Quote\Model\Quote', [], [], '', false);
        $this->quoteRepositoryMock = $this->getMock('Magento\Quote\Model\QuoteRepository', [], [], '', false);
        $this->addressMock = $this->getMock('Magento\Quote\Model\Quote\Address', [], [], '', false);
        $this->dataObjectHelperMock = $this->getMockBuilder('\Magento\Framework\Api\DataObjectHelper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new CartTotalRepository(
            $this->totalsFactoryMock,
            $this->quoteRepositoryMock,
            $this->dataObjectHelperMock
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

        $totals = $this->getMock('Magento\Quote\Model\Cart\Totals', ['setItems'], [], '', false);
        $this->totalsFactoryMock->expects($this->once())->method('create')->willReturn($totals);
        $this->dataObjectHelperMock->expects($this->once())->method('populateWithArray');
        $totals->expects($this->once())->method('setItems');

        $this->model->get($cartId);
    }
}
