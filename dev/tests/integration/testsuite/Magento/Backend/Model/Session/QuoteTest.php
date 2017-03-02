<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Model\Session;

/**
 * Class QuoteTest
 */
class QuoteTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoAppIsolation enabled
     */
    public function testGetQuote()
    {
        /** Preconditions */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $fixtureCustomerId = 1;
        /** @var \Magento\Backend\Model\Session\Quote $backendQuoteSession */
        $backendQuoteSession = $objectManager->get(\Magento\Backend\Model\Session\Quote::class);
        $backendQuoteSession->setCustomerId($fixtureCustomerId);
        /** @var \Magento\Backend\Model\Session\Quote $quoteSession */
        $quoteSession = $objectManager->create(\Magento\Backend\Model\Session\Quote::class);
        $quoteSession->setEntity(new \Magento\Framework\DataObject());

        /** SUT execution */
        $quote = $quoteSession->getQuote();

        /** Ensure that customer data was added to quote correctly */
        $this->assertEquals(
            'John',
            $quote->getCustomer()->getFirstname(),
            'Customer data was set to quote incorrectly.'
        );
    }
}
