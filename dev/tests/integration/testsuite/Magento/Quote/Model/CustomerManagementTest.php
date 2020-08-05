<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Model;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\ObjectManagerInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap as BootstrapHelper;
use PHPUnit\Framework\TestCase;

/**
 * @magentoDbIsolation disabled
 * @magentoAppArea adminhtml
 */
class CustomerManagementTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var CustomerManagement
     */
    private $customerManagemet;

    /**
     * @var CustomerInterface
     */
    private $customer;

    protected function setUp(): void
    {
        $this->objectManager = BootstrapHelper::getObjectManager();
        $this->customerManagemet = $this->objectManager->create(CustomerManagement::class);
        $this->customer = $this->objectManager->create(CustomerInterface::class);
    }

    protected function tearDown(): void
    {
        /** @var CustomerRepositoryInterface $customerRepository */
        $customerRepository = $this->objectManager->create(CustomerRepositoryInterface::class);
        $customer = $customerRepository->get('john1.doe001@test.com');
        $customerRepository->delete($customer);
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/quote.php
     */
    public function testCustomerAddressIdQuote(): void
    {
        $reservedOrderId = 'test01';

        $this->customer->setEmail('john1.doe001@test.com')
        ->setFirstname('doe')
        ->setLastname('john');

        $quote = $this->getQuote($reservedOrderId)->setCustomer($this->customer);
        $this->customerManagemet->populateCustomerInfo($quote);
        self::assertNotNull($quote->getBillingAddress()->getCustomerAddressId());
        self::assertNotNull($quote->getShippingAddress()->getCustomerAddressId());
    }

    /**
     * Gets quote by reserved order ID.
     *
     * @param string $reservedOrderId
     * @return Quote
     */
    private function getQuote(string $reservedOrderId): Quote
    {
        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = $this->objectManager->get(SearchCriteriaBuilder::class);
        $searchCriteria = $searchCriteriaBuilder->addFilter('reserved_order_id', $reservedOrderId)
            ->create();

        /** @var CartRepositoryInterface $quoteRepository */
        $quoteRepository = $this->objectManager->get(CartRepositoryInterface::class);
        $items = $quoteRepository->getList($searchCriteria)
            ->getItems();

        return array_pop($items);
    }
}
