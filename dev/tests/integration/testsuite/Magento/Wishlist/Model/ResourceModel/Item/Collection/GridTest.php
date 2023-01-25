<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Wishlist\Model\ResourceModel\Item\Collection;

use Magento\Customer\Controller\RegistryConstants;
use Magento\Customer\Model\Customer;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Registry;
use Magento\Store\Model\Website;
use PHPUnit\Framework\TestCase;

/**
 * Class to test wishlist collection by customer functionality
 *
 * @magentoAppArea adminhtml
 */
class GridTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var Registry
     */
    private $registryManager;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = ObjectManager::getInstance();
        $this->registryManager = $this->objectManager->get(Registry::class);
    }

    /**
     * Test to load wishlist collection by customer on second website
     *
     * @magentoDbIsolation disabled
     * @magentoDataFixture Magento/Wishlist/_files/wishlist_on_second_website.php
     */
    public function testLoadOnSecondWebsite()
    {
        $customer = $this->loadCustomer();
        $this->registryManager->register(RegistryConstants::CURRENT_CUSTOMER_ID, $customer->getId());

        $gridCollection = $this->objectManager->get(Grid::class);
        $this->assertNotEmpty($gridCollection->getItems());
    }

    /**
     * Load customer in second website
     *
     * @return Customer
     */
    private function loadCustomer(): Customer
    {
        /** @var $website Website */
        $website = $this->objectManager->get(Website::class);
        $website->load('newwebsite', 'code');

        /** @var Customer $customer */
        $customer = $this->objectManager->get(Customer::class);
        $customer->setWebsiteId($website->getId());
        $customer->loadByEmail('customer2@example.com');

        return $customer;
    }
}
