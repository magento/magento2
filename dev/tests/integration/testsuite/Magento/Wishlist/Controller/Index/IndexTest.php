<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Wishlist\Controller\Index;

use Magento\Customer\Model\Session;
use Magento\TestFramework\Helper\Xpath;
use Magento\TestFramework\TestCase\AbstractController;

/**
 * Test wish list on customer account page.
 *
 * @magentoDbIsolation enabled
 * @magentoAppArea frontend
 */
class IndexTest extends AbstractController
{
    /** @var Session */
    private $customerSession;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->customerSession = $this->_objectManager->get(Session::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->customerSession->setCustomerId(null);

        parent::tearDown();
    }

    /**
     * Verify wishlist view action
     *
     * The following is verified:
     * - \Magento\Wishlist\Model\ResourceModel\Item\Collection
     * - \Magento\Wishlist\Block\Customer\Wishlist
     * - \Magento\Wishlist\Block\Customer\Wishlist\Items
     * - \Magento\Wishlist\Block\Customer\Wishlist\Item\Column
     * - \Magento\Wishlist\Block\Customer\Wishlist\Item\Column\Cart
     * - \Magento\Wishlist\Block\Customer\Wishlist\Item\Column\Comment
     * - \Magento\Wishlist\Block\Customer\Wishlist\Button
     * - that \Magento\Wishlist\Block\Customer\Wishlist\Item\Options doesn't throw a fatal error
     *
     * @magentoDataFixture Magento/Wishlist/_files/wishlist.php
     *
     * @return void
     */
    public function testItemColumnBlock(): void
    {
        $this->customerSession->setCustomerId(1);
        $this->dispatch('wishlist/index/index');
        $body = $this->getResponse()->getBody();
        $this->assertEquals(
            1,
            Xpath::getElementsCountForXpath(
                '//img[contains(@src, "small_image.jpg") and @alt = "Simple Product"]',
                $body
            )
        );
        $this->assertEquals(
            1,
            Xpath::getElementsCountForXpath(
                '//textarea[contains(@name, "description")]',
                $body
            )
        );
    }
}
