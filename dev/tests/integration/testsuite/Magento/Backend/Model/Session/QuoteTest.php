<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Backend\Model\Session;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\DataObject;
use Magento\Framework\ObjectManagerInterface;
use Magento\Quote\Model\QuoteFactory;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Class Quote Session Test
 */
class QuoteTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var QuoteFactory
     */
    private $quoteFactory;

    /**
     * @ingeritDoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->quoteFactory = $this->objectManager->create(QuoteFactory::class);
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoAppIsolation enabled
     */
    public function testGetQuote(): void
    {
        $fixtureCustomerId = 1;
        /** @var Quote $backendQuoteSession */
        $backendQuoteSession = $this->objectManager->get(Quote::class);
        $backendQuoteSession->setCustomerId($fixtureCustomerId);
        /** @var Quote $quoteSession */
        $quoteSession = $this->objectManager->create(Quote::class);
        $quoteSession->setEntity(new DataObject());

        /** SUT execution */
        $quote = $quoteSession->getQuote();

        /** Ensure that customer data was added to quote correctly */
        $this->assertEquals(
            'John',
            $quote->getCustomer()->getFirstname(),
            'Customer data was set to quote incorrectly.'
        );
    }

    /**
     * Test quote session id should be null after changing customer
     *
     * @magentoDataFixture Magento/Sales/_files/quote_with_customer.php
     *
     * @return void
     */
    public function testGetQuoteAfterCustomerWasChanged(): void
    {
        $quote = $this->quoteFactory->create();
        $quote->load('test01', 'reserved_order_id');

        //initialize order creation session data
        $backendQuoteSession = $this->objectManager->create(Quote::class);
        $backendQuoteSession->setCustomerId(1);
        $backendQuoteSession->setQuoteId($quote->getId());

        $customerRepository = $this->objectManager->create(CustomerRepositoryInterface::class);
        $customer = $customerRepository->getById(1);
        $customerRepository->save($customer->setFirstName('MrJohn'));

        $this->assertNull($backendQuoteSession->getQuoteId());
    }
}
