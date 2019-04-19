<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Model;

use Magento\TestFramework\Helper\Bootstrap;

/**
 * @magentoDbIsolation disabled
 */
class QuoteValidatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var QuoteValidator
     */
    private $quoteValidator;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->quoteValidator = $this->objectManager->create(QuoteValidator::class);
    }

    /**
     * @magentoDataFixture Magento/Quote/_files/quote_tx_flat.php
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Please check the shipping address information.
     *
     * @return void
     */
    public function testValidateBeforeSubmitShippingAddressInvalid()
    {
        $quote = $this->objectManager->create(Quote::class);
        $quote->load('quote123', 'reserved_order_id');
        $quote->getShippingAddress()->setPostcode('');

        $this->quoteValidator->validateBeforeSubmit($quote);
    }

    /**
     * @magentoDataFixture Magento/Quote/_files/quote_tx_flat.php
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Some addresses can't be used due to the configurations for specific countries.
     *
     * @return void
     */
    public function testValidateBeforeSubmitCountryIsNotAllowed()
    {
        /** @magentoConfigFixture does not allow to change the value for the website scope */
        $this->objectManager->get(
            \Magento\Framework\App\Config\MutableScopeConfigInterface::class
        )->setValue(
            'general/country/allow',
            'US',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $quote = $this->objectManager->create(Quote::class);
        $quote->load('quote123', 'reserved_order_id');
        $quote->getShippingAddress()->setCountryId('AF');

        $this->quoteValidator->validateBeforeSubmit($quote);
    }

    /**
     * @magentoDataFixture Magento/Quote/_files/quote_tx_flat.php
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage The shipping method is missing. Select the shipping method and try again.
     *
     * @return void
     */
    public function testValidateBeforeSubmitShippingMethodInvalid()
    {
        $quote = $this->objectManager->create(Quote::class);
        $quote->load('quote123', 'reserved_order_id');
        $quote->getShippingAddress()->setShippingMethod('NONE');

        $this->quoteValidator->validateBeforeSubmit($quote);
    }

    /**
     * @magentoDataFixture Magento/Quote/_files/quote_tx_flat.php
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Please check the billing address information.
     *
     * @return void
     */
    public function testValidateBeforeSubmitBillingAddressInvalid()
    {
        $quote = $this->objectManager->create(Quote::class);
        $quote->load('quote123', 'reserved_order_id');
        $quote->getBillingAddress()->setTelephone('');

        $this->quoteValidator->validateBeforeSubmit($quote);
    }

    /**
     * @magentoDataFixture Magento/Quote/_files/quote_tx_flat.php
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Enter a valid payment method and try again.
     *
     * @return void
     */
    public function testValidateBeforeSubmitPaymentMethodInvalid()
    {
        $quote = $this->objectManager->create(Quote::class);
        $quote->load('quote123', 'reserved_order_id');
        $quote->getPayment()->setMethod('');

        $this->quoteValidator->validateBeforeSubmit($quote);
    }

    /**
     * @magentoConfigFixture current_store sales/minimum_order/active 1
     * @magentoConfigFixture current_store sales/minimum_order/amount 100
     * @magentoDataFixture Magento/Quote/_files/quote_tx_flat.php
     * @expectedException \Magento\Framework\Exception\LocalizedException
     *
     * @return void
     */
    public function testValidateBeforeSubmitMinimumAmountInvalid()
    {
        $quote = $this->objectManager->create(Quote::class);
        $quote->load('quote123', 'reserved_order_id');
        $quote->getShippingAddress()
            ->setBaseSubtotal(0);
        $this->quoteValidator->validateBeforeSubmit($quote);
    }

    /**
     * @magentoDataFixture Magento/Quote/_files/quote_tx_flat.php
     *
     * @return void
     */
    public function testValidateBeforeSubmitWithoutMinimumOrderAmount()
    {
        $quote = $this->objectManager->create(Quote::class);
        $quote->load('quote123', 'reserved_order_id');
        $this->quoteValidator->validateBeforeSubmit($quote);
    }

    /**
     * @magentoConfigFixture current_store sales/minimum_order/active 1
     * @magentoConfigFixture current_store sales/minimum_order/amount 100
     * @magentoDataFixture Magento/Quote/_files/quote_tx_flat.php
     *
     * @return void
     */
    public function testValidateBeforeSubmitWithMinimumOrderAmount()
    {
        $quote = $this->objectManager->create(Quote::class);
        $quote->load('quote123', 'reserved_order_id');
        $quote->getShippingAddress()
            ->setBaseSubtotal(200);
        $this->quoteValidator->validateBeforeSubmit($quote);
    }
}
