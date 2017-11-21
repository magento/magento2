<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Model\QuoteRepository\Plugin;

use Magento\Authorization\Model\CompositeUserContext;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\App\Area;
use Magento\Quote\Model\QuoteRepository;
use Magento\TestFramework\App\State;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Interception\PluginList;
use PHPUnit\Framework\TestCase;

/**
 * Provide tests for AddCustomerInfo plugin.
 */
class AddCustomerInfoTest extends TestCase
{
    /**
     * Test AddCustomerInfo plugin is registered for Rest Api area.
     */
    public function testAddCustomerInfoIsRegistered()
    {
        /** @var State $state */
        $state = Bootstrap::getObjectManager()->get(State::class);
        $state->setAreaCode(Area::AREA_WEBAPI_REST);
        $pluginInfo = Bootstrap::getObjectManager()->get(PluginList::class)->get(QuoteRepository::class, []);
        self::assertSame(AddCustomerInfo::class, $pluginInfo['add_customer_info']['instance']);
    }

    /**
     * Test AddCustomerInfo plugin adds all necessary customer information, if quote has customer id.
     *
     * @magentoDataFixture Magento/Sales/_files/quote.php
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testAfterSave()
    {
        /** @var State $state */
        $state = Bootstrap::getObjectManager()->get(State::class);
        $state->setAreaCode(Area::AREA_WEBAPI_REST);
        /** @var CustomerRepositoryInterface $customerRepository */
        $customerRepository = Bootstrap::getObjectManager()->get(CustomerRepositoryInterface::class);
        /** @var CustomerInterface $customer */
        $customer = $customerRepository->get('customer@example.com');
        $this->mockUserContext($customer->getId());
        /** @var QuoteRepository $quoteRepository */
        $quoteRepository = Bootstrap::getObjectManager()->create(QuoteRepository::class);
        $quote = $this->loadQuote();
        $quote->setCustomerId($customer->getId());
        $quoteRepository->save($quote);
        $quote = $this->loadQuote();
        self::assertEquals(0, $quote->getCustomerIsGuest());
        self::assertEquals($customer->getId(), $quote->getCustomerId());
        self::assertEquals($customer->getFirstname(), $quote->getCustomerFirstname());
        self::assertEquals($customer->getLastname(), $quote->getCustomerLastname());
        self::assertEquals($customer->getEmail(), $quote->getCustomerEmail());
        self::assertEquals($customer->getGroupId(), $quote->getCustomerGroupId());
    }

    /**
     * @return \Magento\Quote\Model\Quote
     */
    private function loadQuote()
    {
        /** @var $quote \Magento\Quote\Model\Quote */
        $quote = Bootstrap::getObjectManager()->get(\Magento\Quote\Model\Quote::class);
        $quote->load('test01', 'reserved_order_id');

        return $quote;
    }

    /**
     * Mock User context to bypass AccessChangeQuoteControl plugin.
     *
     * @param string $customerId
     * @return void
     */
    private function mockUserContext(string $customerId)
    {
        Bootstrap::getObjectManager()->removeSharedInstance(CompositeUserContext::class);
        /** @var UserContextInterface|\PHPUnit_Framework_MockObject_MockObject $userContext */
        $userContext = $this->getMockBuilder(CompositeUserContext::class)
            ->setMethods(['getUserType', 'getUserId'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $userContext->expects(self::any())
            ->method('getUserType')
            ->willReturn(UserContextInterface::USER_TYPE_CUSTOMER);
        $userContext->expects(self::any())
            ->method('getUserId')
            ->willReturn($customerId);
        Bootstrap::getObjectManager()->addSharedInstance($userContext, CompositeUserContext::class);
    }
}
