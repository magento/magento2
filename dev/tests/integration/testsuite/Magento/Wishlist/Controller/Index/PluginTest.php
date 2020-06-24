<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Wishlist\Controller\Index;

use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Message\MessageInterface;
use Magento\TestFramework\TestCase\AbstractController;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Catalog\Api\ProductRepositoryInterface;

/**
 * Test for wishlist plugin before dispatch
 *
 * @magentoDbIsolation enabled
 * @magentoAppArea frontend
 */
class PluginTest extends AbstractController
{
    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->customerSession = $this->_objectManager->get(CustomerSession::class);
        $this->productRepository = $this->_objectManager->get(ProductRepositoryInterface::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->customerSession->logout();
        $this->customerSession = null;

        parent::tearDown();
    }

    /**
     * Test for adding product to wishlist with invalidate credentials
     *
     * @magentoDataFixture Magento/Catalog/_files/product_simple_xss.php
     * @magentoDataFixture Magento/Customer/_files/customer.php
     *
     * @return void
     */
    public function testAddActionProductWithInvalidCredentials(): void
    {
        $product = $this->productRepository->get('product-with-xss');
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setPostValue(
            [
                'login' => [
                    'username' => 'invalidCustomer@example.com',
                    'password' => 'invalidPassword',
                ],
            ]
        );
        $this->getRequest()->setParams(['product' => $product->getId(), 'nocookie' => 1]);
        $this->dispatch('wishlist/index/add');
        $this->assertArrayNotHasKey('login', $this->customerSession->getBeforeWishlistRequest());
        $expectedMessage = 'You must login or register to add items to your wishlist.';
        $this->assertSessionMessages($this->equalTo([(string)__($expectedMessage)]), MessageInterface::TYPE_ERROR);
    }

    /**
     * @magentoConfigFixture current_store wishlist/general/active 0
     * @magentoDataFixture Magento/Customer/_files/customer.php
     *
     * @return void
     */
    public function testWithDisabledWishList(): void
    {
        $this->customerSession->setCustomerId(1);
        $this->dispatch('wishlist/index/index');
        $this->assert404NotFound();
    }
}
