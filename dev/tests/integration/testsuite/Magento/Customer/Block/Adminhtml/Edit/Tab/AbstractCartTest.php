<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Block\Adminhtml\Edit\Tab;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Controller\RegistryConstants;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\LayoutInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\QuoteFactory;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Base class for testing \Magento\Customer\Block\Adminhtml\Edit\Tab\Cart block
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class AbstractCartTest extends TestCase
{
    const CUSTOMER_ID_VALUE = 1234;

    /** @var Registry */
    private $registry;

    /** @var Cart */
    protected $block;

    /** @var ObjectManagerInterface */
    protected $objectManager;

    /** @var CartRepositoryInterface */
    protected $quoteRepository;

    /** @var CustomerRepositoryInterface */
    private $customerRepository;

    /** @var QuoteFactory */
    private $quoteFactory;

    /**
     * @inheritdoc
     */
    public function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->registry = $this->objectManager->get(Registry::class);
        $this->registerCustomerId(self::CUSTOMER_ID_VALUE);
        $this->block = $this->objectManager->get(LayoutInterface::class)->createBlock(Cart::class);
        $this->quoteRepository = $this->objectManager->get(CartRepositoryInterface::class);
        $this->customerRepository = $this->objectManager->get(CustomerRepositoryInterface::class);
        $this->quoteFactory = $this->objectManager->get(QuoteFactory::class);
    }

    /**
     * @inheritdoc
     */
    public function tearDown(): void
    {
        $this->registry->unregister(RegistryConstants::CURRENT_CUSTOMER_ID);
    }

    /**
     * Check that the expected items of the shopping cart are in the block
     *
     * @param string $customerEmail
     * @return void
     */
    protected function processCheckQuoteItems(string $customerEmail): void
    {
        $customer = $this->customerRepository->get($customerEmail);
        $this->registerCustomerId((int)$customer->getId());
        $this->block->toHtml();

        $quoteItemIds = $this->getQuoteItemIds((int)$customer->getId());
        $this->assertCount(
            count($quoteItemIds),
            $this->block->getPreparedCollection(),
            "Item's count in the customer cart grid block doesn't match expected count."
        );
        $this->assertEmpty(
            array_diff(
                $this->block->getPreparedCollection()->getAllIds(),
                $quoteItemIds
            ),
            "Items in the customer cart grid block doesn't match expected items."
        );
    }

    /**
     * Checks that customer's shopping cart block is empty
     *
     * @param string $customerEmail
     * @return void
     */
    protected function processCheckWithoutQuoteItems(string $customerEmail): void
    {
        $customer = $this->customerRepository->get($customerEmail);
        $this->registerCustomerId((int)$customer->getId());
        $this->block->toHtml();

        $this->assertCount(
            0,
            $this->block->getPreparedCollection(),
            "Item's count in the customer cart grid block doesn't match expected count."
        );
    }

    /**
     * Add customer id to registry.
     *
     * @param int $customerId
     * @return void
     */
    private function registerCustomerId(int $customerId): void
    {
        $this->registry->unregister(RegistryConstants::CURRENT_CUSTOMER_ID);
        $this->registry->register(RegistryConstants::CURRENT_CUSTOMER_ID, $customerId);
    }

    /**
     * Get shopping cart quote item identifiers by customer id.
     *
     * @param int $customerId
     * @return array
     */
    private function getQuoteItemIds(int $customerId): array
    {
        $ids = [];
        /** @var Quote $quote */
        $quote = $this->quoteRepository->getForCustomer($customerId);
        /** @var Item $item */
        foreach ($quote->getItems() as $item) {
            $ids[] = $item->getId();
        }

        return $ids;
    }
}
