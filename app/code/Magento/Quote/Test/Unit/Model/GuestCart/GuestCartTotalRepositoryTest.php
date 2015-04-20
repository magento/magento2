<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Test\Unit\Model\GuestCart;

class GuestCartTotalRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Quote\Model\GuestCart\GuestCartTotalRepository
     */
    protected $model;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $cartTotalRepository;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteIdMaskFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteIdMaskMock;

    /**
     * @var string
     */
    protected $maskedCartId = 'f216207248d65c789b17be8545e0aa73';

    /**
     * @var int
     */
    protected $cartId = 12;

    public function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->quoteIdMaskFactoryMock = $this->getMockBuilder('Magento\Quote\Model\QuoteIdMaskFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->quoteIdMaskMock = $this->getMockBuilder('Magento\Quote\Model\QuoteIdMask')
            ->disableOriginalConstructor()
            ->getMock();
        $this->cartTotalRepository = $this->getMockBuilder('Magento\Quote\Api\CartTotalRepositoryInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = $this->objectManager->getObject(
            'Magento\Quote\Model\GuestCart\GuestCartTotalRepository',
            [
                'cartTotalRepository' => $this->cartTotalRepository,
                'quoteIdMaskFactory' => $this->quoteIdMaskFactoryMock,
            ]
        );

        $this->quoteIdMaskFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->quoteIdMaskMock);
        $this->quoteIdMaskMock->expects($this->once())
            ->method('load')
            ->with($this->maskedCartId, 'masked_id')
            ->willReturn($this->quoteIdMaskMock);
        $this->quoteIdMaskMock->expects($this->once())
            ->method('getId')
            ->willReturn($this->cartId);
    }

    public function testGetTotals()
    {
        $retValue = 'retValue';

        $this->cartTotalRepository->expects($this->once())
            ->method('get')
            ->with($this->cartId)
            ->will($this->returnValue($retValue));
        $this->assertSame($retValue, $this->model->get($this->maskedCartId));
    }
}
