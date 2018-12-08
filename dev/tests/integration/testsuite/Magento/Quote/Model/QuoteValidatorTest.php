<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Model;

<<<<<<< HEAD
use Magento\TestFramework\Helper\Bootstrap;

/**
 * @magentoDbIsolation disabled
=======
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Model\Quote\Address\Rate;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Class QuoteValidatorTest.
 *
 * @magentoDbIsolation enabled
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
 */
class QuoteValidatorTest extends \PHPUnit\Framework\TestCase
{
    /**
<<<<<<< HEAD
     * @var ObjectManager
     */
    private $objectManager;

    /**
=======
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
     * @var QuoteValidator
     */
    private $quoteValidator;

    /**
     * @inheritdoc
     */
<<<<<<< HEAD
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
=======
    public function setUp()
    {
        $this->quoteValidator = Bootstrap::getObjectManager()->create(QuoteValidator::class);
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Please check the shipping address information.
     */
    public function testValidateBeforeSubmitShippingAddressInvalid()
    {
        $quote = $this->getQuote();
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
        $quote->getShippingAddress()->setPostcode('');

        $this->quoteValidator->validateBeforeSubmit($quote);
    }

    /**
<<<<<<< HEAD
     * @magentoDataFixture Magento/Quote/_files/quote_tx_flat.php
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Some addresses can't be used due to the configurations for specific countries.
     *
     * @return void
=======
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Some addresses can't be used due to the configurations for specific countries.
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
     */
    public function testValidateBeforeSubmitCountryIsNotAllowed()
    {
        /** @magentoConfigFixture does not allow to change the value for the website scope */
<<<<<<< HEAD
        $this->objectManager->get(
=======
        Bootstrap::getObjectManager()->get(
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
            \Magento\Framework\App\Config\MutableScopeConfigInterface::class
        )->setValue(
            'general/country/allow',
            'US',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
<<<<<<< HEAD
        $quote = $this->objectManager->create(Quote::class);
        $quote->load('quote123', 'reserved_order_id');
=======
        $quote = $this->getQuote();
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
        $quote->getShippingAddress()->setCountryId('AF');

        $this->quoteValidator->validateBeforeSubmit($quote);
    }

    /**
<<<<<<< HEAD
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
=======
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage The shipping method is missing. Select the shipping method and try again.
     */
    public function testValidateBeforeSubmitShippingMethodInvalid()
    {
        $quote = $this->getQuote();
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
        $quote->getShippingAddress()->setShippingMethod('NONE');

        $this->quoteValidator->validateBeforeSubmit($quote);
    }

    /**
<<<<<<< HEAD
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
=======
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Please check the billing address information.
     */
    public function testValidateBeforeSubmitBillingAddressInvalid()
    {
        $quote = $this->getQuote();
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
        $quote->getBillingAddress()->setTelephone('');

        $this->quoteValidator->validateBeforeSubmit($quote);
    }

    /**
<<<<<<< HEAD
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
=======
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Enter a valid payment method and try again.
     */
    public function testValidateBeforeSubmitPaymentMethodInvalid()
    {
        $quote = $this->getQuote();
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
        $quote->getPayment()->setMethod('');

        $this->quoteValidator->validateBeforeSubmit($quote);
    }

    /**
<<<<<<< HEAD
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
=======
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @magentoConfigFixture current_store sales/minimum_order/active 1
     * @magentoConfigFixture current_store sales/minimum_order/amount 100
     */
    public function testValidateBeforeSubmitMinimumAmountInvalid()
    {
        $quote = $this->getQuote();
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
        $quote->getShippingAddress()
            ->setBaseSubtotal(0);
        $this->quoteValidator->validateBeforeSubmit($quote);
    }

    /**
<<<<<<< HEAD
     * @magentoDataFixture Magento/Quote/_files/quote_tx_flat.php
     *
=======
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
     * @return void
     */
    public function testValidateBeforeSubmitWithoutMinimumOrderAmount()
    {
<<<<<<< HEAD
        $quote = $this->objectManager->create(Quote::class);
        $quote->load('quote123', 'reserved_order_id');
        $this->quoteValidator->validateBeforeSubmit($quote);
=======
        $this->quoteValidator->validateBeforeSubmit($this->getQuote());
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
    }

    /**
     * @magentoConfigFixture current_store sales/minimum_order/active 1
     * @magentoConfigFixture current_store sales/minimum_order/amount 100
<<<<<<< HEAD
     * @magentoDataFixture Magento/Quote/_files/quote_tx_flat.php
     *
     * @return void
     */
    public function testValidateBeforeSubmitWithMinimumOrderAmount()
    {
        $quote = $this->objectManager->create(Quote::class);
        $quote->load('quote123', 'reserved_order_id');
=======
     */
    public function testValidateBeforeSubmitWithMinimumOrderAmount()
    {
        $quote = $this->getQuote();
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
        $quote->getShippingAddress()
            ->setBaseSubtotal(200);
        $this->quoteValidator->validateBeforeSubmit($quote);
    }
<<<<<<< HEAD
=======

    /**
     * Checks a case when the default website has country restrictions and the quote created
     * for the another website with different country restrictions.
     *
     * @magentoDataFixture Magento/Quote/Fixtures/quote_sec_website.php
     * @magentoDbIsolation disabled
     */
    public function testValidateBeforeSubmit()
    {
        $quote = $this->getQuoteById('0000032134');
        $this->quoteValidator->validateBeforeSubmit($quote);
    }

    /**
     * @return Quote
     */
    private function getQuote(): Quote
    {
        /** @var Quote $quote */
        $quote = Bootstrap::getObjectManager()->create(Quote::class);

        /** @var AddressInterface $billingAddress */
        $billingAddress = Bootstrap::getObjectManager()->create(AddressInterface::class);
        $billingAddress->setFirstname('Joe')
            ->setLastname('Doe')
            ->setCountryId('US')
            ->setRegion('TX')
            ->setCity('Austin')
            ->setStreet('1000 West Parmer Line')
            ->setPostcode('11501')
            ->setTelephone('123456789');
        $quote->setBillingAddress($billingAddress);

        /** @var AddressInterface $shippingAddress */
        $shippingAddress = Bootstrap::getObjectManager()->create(AddressInterface::class);
        $shippingAddress->setFirstname('Joe')
        ->setLastname('Doe')
        ->setCountryId('US')
        ->setRegion('TX')
        ->setCity('Austin')
        ->setStreet('1000 West Parmer Line')
        ->setPostcode('11501')
        ->setTelephone('123456789');
        $quote->setShippingAddress($shippingAddress);

        $quote->getShippingAddress()
            ->setShippingMethod('flatrate_flatrate')
            ->setCollectShippingRates(true);
        /** @var Rate $shippingRate */
        $shippingRate = Bootstrap::getObjectManager()->create(Rate::class);
        $shippingRate->setMethod('flatrate')
            ->setCarrier('flatrate')
            ->setPrice('5')
            ->setCarrierTitle('Flat Rate')
            ->setCode('flatrate_flatrate');
        $quote->getShippingAddress()
            ->addShippingRate($shippingRate);

        $quote->getPayment()->setMethod('CC');

        /** @var QuoteRepository $quoteRepository */
        $quoteRepository = Bootstrap::getObjectManager()->create(QuoteRepository::class);
        $quoteRepository->save($quote);

        return $quote;
    }

    /**
     * Gets quote entity by reserved order id.
     *
     * @param string $reservedOrderId
     * @return Quote
     */
    private function getQuoteById(string $reservedOrderId): Quote
    {
        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = Bootstrap::getObjectManager()->get(SearchCriteriaBuilder::class);
        $searchCriteria = $searchCriteriaBuilder->addFilter('reserved_order_id', $reservedOrderId)
            ->create();

        /** @var CartRepositoryInterface $repository */
        $repository = Bootstrap::getObjectManager()->get(CartRepositoryInterface::class);
        $items = $repository->getList($searchCriteria)
            ->getItems();

        return array_pop($items);
    }
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
}
