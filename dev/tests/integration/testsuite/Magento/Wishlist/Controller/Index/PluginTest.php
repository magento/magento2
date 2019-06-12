<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Wishlist\Controller\Index;

use Magento\TestFramework\TestCase\AbstractController;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Catalog\Api\ProductRepositoryInterface;

/**
 * Test for wishlist plugin before dispatch
 */
class PluginTest extends AbstractController
{
    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();
        $this->customerSession = $this->_objectManager->get(CustomerSession::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown()
    {
        $this->customerSession->logout();
        $this->customerSession = null;
        parent::tearDown();
    }

    /**
     * Test for adding product to wishlist with invalidate credentials
     *
     * @return void
     * @magentoDataFixture Magento/Catalog/_files/product_simple_xss.php
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoAppArea frontend
     */
    public function testAddActionProductWithInvalidCredentials(): void
    {
        $this->getRequest()->setMethod('POST');
        $this->getRequest()->setPostValue(
            [
                'login' => [
                    'username' => 'invalidCustomer@example.com',
                    'password' => 'invalidPassword',
                ],
            ]
        );

        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $this->_objectManager->get(ProductRepositoryInterface::class);

        $product = $productRepository->get('product-with-xss');

        $this->dispatch('wishlist/index/add/product/' . $product->getId() . '?nocookie=1');

        $this->assertArrayNotHasKey('login', $this->customerSession->getBeforeWishlistRequest());
    }
}
