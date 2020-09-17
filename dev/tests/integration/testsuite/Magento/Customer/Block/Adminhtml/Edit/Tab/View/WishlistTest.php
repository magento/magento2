<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Block\Adminhtml\Edit\Tab\View;

use Magento\Customer\Controller\RegistryConstants;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\LayoutInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Tests for customer wish list tab.
 *
 * @magentoAppArea adminhtml
 * @magentoDbIsolation disabled
 */
class WishlistTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var Registry */
    private $registry;

    /** @var Wishlist */
    private $block;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->registry = $this->objectManager->get(Registry::class);
        $this->block = $this->objectManager->get(LayoutInterface::class)->createBlock(Wishlist::class);
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
     * @magentoDataFixture Magento/Wishlist/_files/wishlist.php
     *
     * @return void
     */
    public function testWishListGrid(): void
    {
        $this->registerCustomerId(1);
        $this->assertCount(1, $this->block->getPreparedCollection());
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
}
