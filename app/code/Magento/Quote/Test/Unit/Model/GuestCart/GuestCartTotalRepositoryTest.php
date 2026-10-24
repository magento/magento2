<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Model\GuestCart;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Quote\Api\CartTotalRepositoryInterface;
use Magento\Quote\Model\GuestCart\GuestCartTotalRepository;
use Magento\Quote\Model\QuoteIdMask;
use Magento\Quote\Model\QuoteIdMaskFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GuestCartTotalRepositoryTest extends TestCase
{
    use MockCreationTrait;
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

        $this->cartTotalRepository = $this->createMock(CartTotalRepositoryInterface::class);

        $this->maskedCartId = 'f216207248d65c789b17be8545e0aa73';
        $this->cartId = 123;

        // Create QuoteIdMask mock
        $this->quoteIdMaskMock = $this->createPartialMockWithReflection(QuoteIdMask::class, ["load", "getQuoteId"]);
        $this->quoteIdMaskMock->method("load")->willReturnSelf();
        $this->quoteIdMaskMock->method("getQuoteId")->willReturn($this->cartId);
        
        // Create QuoteIdMaskFactory mock
        $this->quoteIdMaskFactoryMock = $this->createMock(QuoteIdMaskFactory::class);
        $this->quoteIdMaskFactoryMock->method("create")->willReturn($this->quoteIdMaskMock);

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
