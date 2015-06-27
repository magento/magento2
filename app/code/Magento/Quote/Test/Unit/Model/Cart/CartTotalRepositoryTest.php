<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Test\Unit\Model\Cart;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class CartTotalRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $converterMock;

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
        $this->objectManager = new ObjectManager($this);
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
        $this->converterMock = $this->getMock(
            'Magento\Quote\Model\Cart\Totals\ItemConverter',
            [],
            [],
            '',
            false
        );

        $this->model = $this->objectManager->getObject(
            '\Magento\Quote\Model\Cart\CartTotalRepository',
            [
                'totalsFactory' => $this->totalsFactoryMock,
                'quoteRepository' => $this->quoteRepositoryMock,
                'dataObjectHelper' => $this->dataObjectHelperMock,
                'converter' => $this->converterMock,
            ]
        );
    }

    public function testGet()
    {
        $cartId = 12;
        $itemMock = $this->getMock(
            'Magento\Quote\Model\Quote\Item',
            [],
            [],
            '',
            false
        );
        $visibleItems = [
            11 => $itemMock,
        ];
        $itemArray = [
            'name' => 'item',
            'options' => [ 4 => ['label' => 'justLabel']],
        ];
        $this->quoteRepositoryMock->expects($this->once())->method('getActive')->with($cartId)
            ->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->once())->method('getShippingAddress')->willReturn($this->addressMock);
        $this->addressMock->expects($this->once())->method('getData')->willReturn(['addressData']);
        $this->quoteMock->expects($this->once())->method('getData')->willReturn(['quoteData']);

        $this->quoteMock->expects($this->once())->method('getAllVisibleItems')->willReturn($visibleItems);

        $totalsMock = $this->getMock('Magento\Quote\Model\Cart\Totals', ['setItems'], [], '', false);
        $this->totalsFactoryMock->expects($this->once())->method('create')->willReturn($totalsMock);
        $this->dataObjectHelperMock->expects($this->once())->method('populateWithArray');
        $this->converterMock->expects($this->once())
            ->method('modelToDataObject')
            ->with($itemMock)
            ->willReturn($itemArray);

        //back in get()
        $totalsMock->expects($this->once())->method('setItems')->with(
            [
            11 => $itemArray,
            ]
        );

        $this->assertEquals($totalsMock, $this->model->get($cartId));
    }
}
