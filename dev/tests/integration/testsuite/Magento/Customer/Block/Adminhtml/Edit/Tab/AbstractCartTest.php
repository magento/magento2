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
use Magento\Quote\Model\ResourceModel\Quote\Item\Collection as ItemCollection;
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
    public function setUp()
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
    public function tearDown()
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

        $quoteItemsCollection = $this->getQuoteItemsCollection((int)$customer->getId());
        $this->assertCount(
            $quoteItemsCollection->count(),
            $this->block->getPreparedCollection(),
            "Item's count in the customer cart grid block doesn't match expected count."
        );
        $this->assertEmpty(
            array_diff(
                $this->block->getPreparedCollection()->getColumnValues(Item::KEY_ITEM_ID),
                $quoteItemsCollection->getColumnValues(Item::KEY_ITEM_ID)
            ),
            "Items in the customer cart grid block doesn't match expected items."
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
     * Get shopping cart quote items collection by customer id.
     *
     * @param int $customerId
     * @return ItemCollection
     */
    private function getQuoteItemsCollection(int $customerId): ItemCollection
    {
        try {
            /** @var Quote $quote */
            $quote = $this->quoteRepository->getForCustomer($customerId);
        } catch (NoSuchEntityException $e) {
            $quote = $this->quoteFactory->create();
        }
        $quoteItemsCollection = $quote->getItemsCollection(false);

        if ($quote->getId()) {
            $quoteItemsCollection->addFieldToFilter('parent_item_id', ['null' => true]);
        }

        return $quoteItemsCollection;
    }
}
