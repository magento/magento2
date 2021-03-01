<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Test\Unit\Model\StoreSwitcher;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Checkout\Model\StoreSwitcher\RedirectDataPreprocessor;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Quote\Model\Quote;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreSwitcher\ContextInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RedirectDataPreprocessorTest extends TestCase
{
    /**
     * @var CustomerSession
     */
    private $customerSession;
    /**
     * @var CheckoutSession
     */
    private $checkoutSession;
    /**
     * @var RedirectDataPreprocessor
     */
    private $model;
    /**
     * @var ContextInterface|MockObject
     */
    private $context;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->customerSession = $this->createMock(CustomerSession::class);
        $this->checkoutSession = $this->createMock(CheckoutSession::class);
        $this->model = new RedirectDataPreprocessor(
            $this->customerSession,
            $this->checkoutSession
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
     */
    public function testProcess(array $mock, array $data): void
    {
        $this->customerSession->method('isLoggedIn')
            ->willReturn($mock['isLoggedIn']);
        $this->checkoutSession->method('getQuoteId')
            ->willReturn($mock['getQuoteId']);
        $this->checkoutSession->method('getQuote')
            ->willReturnCallback(
                function () use ($mock) {
                    return $this->createConfiguredMock(
                        Quote::class,
                        [
                            'getIsActive' => $mock['getIsActive'] ?? true,
                            'getId' => $mock['getQuoteId'],
                            'getSharedStoreIds' => !($mock['getQuoteId'] % 2) ? [1, 2] : [2],
                        ]
                    );
                }
            );
        $this->assertEquals($data, $this->model->process($this->context, []));
    }

    /**
     * @return array
     */
    public function processDataProvider(): array
    {
        return [
            [
                ['isLoggedIn' => true, 'getQuoteId' => 1],
                []
            ],
            [
                ['isLoggedIn' => false, 'getQuoteId' => null],
                []
            ],
            [
                ['isLoggedIn' => false, 'getQuoteId' => 1],
                []
            ],
            [
                ['isLoggedIn' => false, 'getQuoteId' => 2],
                ['quote_id' => 2]
            ],
        ];
    }
}
