<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Block\Adminhtml\Edit\Tab\Cart;

use Magento\Customer\Block\Adminhtml\Edit\Tab\AbstractCartTest;
use Magento\Framework\Module\Manager;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\ResourceModel\Quote\CollectionFactory;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Class checks customer's shopping cart block with bundle product.
 *
 * @see \Magento\Customer\Block\Adminhtml\Edit\Tab\Cart
 * @magentoAppArea adminhtml
 */
class CartBundleTest extends AbstractCartTest
{
    /** @var CollectionFactory */
    private $quoteCollectionFactory;

    /**
     * @inheritdoc
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->quoteCollectionFactory = $this->objectManager->get(CollectionFactory::class);
    }

    /**
     * @inheritdoc
     */
    public static function setUpBeforeClass(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        /** @var Manager $moduleManager */
        $moduleManager = $objectManager->get(Manager::class);
        //This check is needed because Customer independent of Magento_Bundle
        if (!$moduleManager->isEnabled('Magento_Bundle')) {
            self::markTestSkipped('Magento_Bundle module disabled.');
        }
    }

    /**
     * @magentoDataFixture Magento/Bundle/_files/quote_with_bundle_and_options.php
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @return void
     */
    public function testBundleProductView(): void
    {
        $quoteCollection = $this->quoteCollectionFactory->create();
        $quoteCollection->addFieldToFilter('reserved_order_id', 'test_cart_with_bundle_and_options');
        /** @var Quote $quote */
        $quote = $quoteCollection->getFirstItem();
        $this->assertNotEmpty($quote->getId());
        $quote->setCustomerId(1);
        $this->quoteRepository->save($quote);
        $this->processCheckQuoteItems('customer@example.com');
    }
}
