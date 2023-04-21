<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Test\Unit\Model\StoreSwitcher;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Checkout\Model\StoreSwitcher\RedirectDataPostprocessor;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreSwitcher\ContextInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class RedirectDataPostprocessorTest extends TestCase
{
    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;
    /**
     * @var ContextInterface|MockObject
     */
    private $context;
    /**
     * @var RedirectDataPostprocessor
     */
    private $model;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->quoteRepository = $this->createMock(CartRepositoryInterface::class);
        $this->customerSession = $this->createMock(CustomerSession::class);
        $this->checkoutSession = $this->createMock(CheckoutSession::class);
        $logger = $this->createMock(LoggerInterface::class);
        $this->model = new RedirectDataPostprocessor(
            $this->quoteRepository,
            $this->customerSession,
            $this->checkoutSession,
            $logger
        );

        $store1 = $this->createConfiguredMock(
            StoreInterface::class,
            [
                'getCode' => 'en',
                'getId' => 1,
            ]
        );
        $store2 = $this->createConfiguredMock(
            StoreInterface::class,
            [
                'getCode' => 'fr',
                'getId' => 2,
            ]
        );
        $this->context = $this->createConfiguredMock(
            ContextInterface::class,
            [
                'getFromStore' => $store2,
                'getTargetStore' => $store1,
            ]
        );
    }

    /**
     * @dataProvider processDataProvider
     * @param array $mock
     * @param array $data
     * @param bool $isQuoteSet
     */
    public function testProcess(array $mock, array $data, bool $isQuoteSet): void
    {
        $this->customerSession->method('isLoggedIn')
            ->willReturn($mock['isLoggedIn']);
        $this->checkoutSession->method('getQuoteId')
            ->willReturn($mock['getQuoteId']);
        $this->checkoutSession->method('getQuote')
            ->willReturnCallback(
                function () use ($mock) {
                    return $this->createQuoteMock($mock);
                }
            );
        $this->quoteRepository->method('get')
            ->willReturnCallback(
                function ($id) use ($mock) {
                    return $this->createQuoteMock(array_merge($mock, ['getQuoteId' => $id]));
                }
            );
        $this->checkoutSession->expects($isQuoteSet ? $this->once() : $this->never())
            ->method('setQuoteId')
            ->with($data['quote_id'] ?? null);

        $this->model->process($this->context, $data);
    }

    /**
     * @return array
     */
    public function processDataProvider(): array
    {
        return [
            [
                ['isLoggedIn' => false, 'getQuoteId' => 4],
                ['quote_id' => 2],
                false
            ],
            [
                ['isLoggedIn' => true, 'getQuoteId' => null],
                ['quote_id' => 2],
                false
            ],
            [
                ['isLoggedIn' => false, 'getQuoteId' => null],
                ['quote_id' => 1],
                false
            ],
            [
                ['isLoggedIn' => false, 'getQuoteId' => null, 'getIsActive' => false],
                ['quote_id' => 2],
                false
            ],
            [
                ['isLoggedIn' => false, 'getQuoteId' => null],
                ['quote_id' => 2],
                true
            ],
        ];
    }

    /**
     * @param array $mock
     * @return Quote
     */
    private function createQuoteMock(array $mock): Quote
    {
        return $this->createConfiguredMock(
            Quote::class,
            [
                'getIsActive' => $mock['getIsActive'] ?? true,
                'getId' => $mock['getQuoteId'],
                'getSharedStoreIds' => !($mock['getQuoteId'] % 2) ? [1, 2] : [2],
            ]
        );
    }
}
