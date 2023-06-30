<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\QuoteGraphQl\Test\Unit\Model\Cart;

use Magento\Checkout\Api\Exception\PaymentProcessingRateLimitExceededException;
use Magento\Checkout\Api\PaymentSavingRateLimiterInterface;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Model\Quote;
use Magento\QuoteGraphQl\Model\Cart\SetPaymentMethodOnCart;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SetPaymentMethodOnCartTest extends TestCase
{
    /**
     * @var SetPaymentMethodOnCart
     */
    private $model;

    /**
     * @var PaymentSavingRateLimiterInterface|MockObject
     */
    private $rateLimiterMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $objectManager = new ObjectManager($this);
        $this->rateLimiterMock = $this->getMockForAbstractClass(PaymentSavingRateLimiterInterface::class);
        $this->model = $objectManager->getObject(
            SetPaymentMethodOnCart::class,
            ['savingRateLimiter' => $this->rateLimiterMock]
        );
    }

    /**
     * Verify that the method is rate-limited.
     *
     * @return void
     */
    public function testLimited(): void
    {
        $this->rateLimiterMock->method('limit')
            ->willThrowException(new PaymentProcessingRateLimitExceededException(__('Error')));

        //There will be en error if the limiter won't stop the execution
        $this->model->execute($this->createMock(Quote::class), []);
    }
}
