<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
    protected $maskedCartId;

    /**
     * @var int
     */
    protected $cartId;

    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->cartTotalRepository = $this->getMockBuilder(\Magento\Quote\Api\CartTotalRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->maskedCartId = 'f216207248d65c789b17be8545e0aa73';
        $this->cartId = 123;

        $guestCartTestHelper = new GuestCartTestHelper($this);
        list($this->quoteIdMaskFactoryMock, $this->quoteIdMaskMock) = $guestCartTestHelper->mockQuoteIdMask(
            $this->maskedCartId,
            $this->cartId
        );

        $this->model = $this->objectManager->getObject(
            \Magento\Quote\Model\GuestCart\GuestCartTotalRepository::class,
            [
                'cartTotalRepository' => $this->cartTotalRepository,
                'quoteIdMaskFactory' => $this->quoteIdMaskFactoryMock,
            ]
        );
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
