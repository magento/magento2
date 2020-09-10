<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Block\Adminhtml\Edit\Tab\Cart;

use Magento\Customer\Block\Adminhtml\Edit\Tab\Cart;
use Magento\Customer\Controller\RegistryConstants;
use Magento\Framework\Data\Collection;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\LayoutInterface;
use Magento\Quote\Model\QuoteRepository;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Store\ExecuteInStoreContext;
use PHPUnit\Framework\TestCase;

/**
 * Class checks that shopping cart grid can be filtered
 *
 * @see \Magento\Customer\Block\Adminhtml\Edit\Tab\Cart::_prepareCollection()
 *
 * @magentoAppArea adminhtml
 * @magentoDbIsolation disabled
 */
class CollectionTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var ExecuteInStoreContext */
    private $executeInStoreContext;

    /** @var Registry */
    private $registry;

    /** @var LayoutInterface */
    private $layout;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->executeInStoreContext = $this->objectManager->get(ExecuteInStoreContext::class);
        $this->registry = $this->objectManager->get(Registry::class);
        $this->layout = $this->objectManager->get(LayoutInterface::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->registry->unregister(RegistryConstants::CURRENT_CUSTOMER_ID);

        parent::tearDown();
    }

    /**
     * @magentoDataFixture Magento/Checkout/_files/quote_with_address_saved.php
     * @magentoDataFixture Magento/Checkout/_files/customer_quote_on_second_website.php
     *
     * @return void
     */
    public function testCollectionOnDifferentStores(): void
    {
        $this->registry->unregister(RegistryConstants::CURRENT_CUSTOMER_ID);
        $this->registry->register(RegistryConstants::CURRENT_CUSTOMER_ID, 1);
        $collectionFirstWebsite = $this->executeInStoreContext->execute(
            'default',
            [$this->layout->createBlock(Cart::class), 'getPreparedCollection']
        );
        $this->assertCollection($collectionFirstWebsite, 'Simple Product');
        $this->objectManager->removeSharedInstance(QuoteRepository::class);
        $collectionSecondWebsite = $this->executeInStoreContext->execute(
            'fixture_second_store',
            [$this->layout->createBlock(Cart::class), 'getPreparedCollection']
        );
        $this->assertCollection($collectionSecondWebsite, 'Simple Product on second website');
    }

    /**
     * Check is collection match expected value
     *
     * @param Collection $collection
     * @param string $itemName
     * @return void
     */
    private function assertCollection(Collection $collection, string $itemName): void
    {
        $this->assertCount(1, $collection, 'Collection size does not match expected value');
        $this->assertEquals($itemName, $collection->getFirstItem()->getName());
    }
}
