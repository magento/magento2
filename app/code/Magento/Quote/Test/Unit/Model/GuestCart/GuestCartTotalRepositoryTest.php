<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Model\GuestCart;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Api\CartTotalRepositoryInterface;
use Magento\Quote\Model\GuestCart\GuestCartTotalRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GuestCartTotalRepositoryTest extends TestCase
{
    /**
     * @var GuestCartTotalRepository
     */
    protected $model;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var MockObject
     */
    protected $cartTotalRepository;

    /**
     * @var MockObject
     */
    protected $quoteIdMaskFactoryMock;

    /**
     * @var MockObject
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

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->cartTotalRepository = $this->getMockBuilder(CartTotalRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->maskedCartId = 'f216207248d65c789b17be8545e0aa73';
        $this->cartId = 123;

        $guestCartTestHelper = new GuestCartTestHelper($this);
        list($this->quoteIdMaskFactoryMock, $this->quoteIdMaskMock) = $guestCartTestHelper->mockQuoteIdMask(
            $this->maskedCartId,
            $this->cartId
        );

        $this->model = $this->objectManager->getObject(
            GuestCartTotalRepository::class,
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
            ->willReturn($retValue);
        $this->assertSame($retValue, $this->model->get($this->maskedCartId));
    }
}
