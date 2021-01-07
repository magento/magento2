<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Block\Adminhtml\Edit\Tab\Cart;

use Magento\Customer\Block\Adminhtml\Edit\Tab\AbstractCartTest;
use Magento\Framework\Module\Manager;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Class checks customer's shopping cart block with configurable product.
 *
 * @see \Magento\Customer\Block\Adminhtml\Edit\Tab\Cart
 * @magentoAppArea adminhtml
 */
class CartConfigurableTest extends AbstractCartTest
{
    /**
     * @inheritdoc
     */
    public static function setUpBeforeClass(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        /** @var Manager $moduleManager */
        $moduleManager = $objectManager->get(Manager::class);
        //This check is needed because Customer independent of Magento_ConfigurableProduct
        if (!$moduleManager->isEnabled('Magento_ConfigurableProduct')) {
            self::markTestSkipped('Magento_ConfigurableProduct module disabled.');
        }
    }

    /**
     * @magentoDataFixture Magento/ConfigurableProduct/_files/customer_quote_with_items_configurable_product.php
     * @return void
     */
    public function testConfigurableProductView(): void
    {
        $this->processCheckQuoteItems('customer_uk_address@test.com');
    }
}
