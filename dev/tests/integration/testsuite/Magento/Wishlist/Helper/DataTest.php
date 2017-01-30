<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Wishlist\Helper;

class DataTest extends \Magento\TestFramework\TestCase\AbstractController
{
    /**
     * @var Data
     */
    private $_wishlistHelper;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * Get required instance
     */
    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->_wishlistHelper = $this->objectManager->get('Magento\Wishlist\Helper\Data');
        $this->_customerSession = $this->objectManager->get('Magento\Customer\Model\Session');
    }

    /**
     * Clear wishlist helper property
     */
    protected function tearDown()
    {
        $this->_wishlistHelper = null;
        if ($this->_customerSession->isLoggedIn()) {
            $this->_customerSession->logout();
        }
    }

    public function testGetAddParams()
    {
        $product = $this->objectManager->get('Magento\Catalog\Model\Product');
        $product->setId(11);
        $json = $this->_wishlistHelper->getAddParams($product);
        $params = (array)json_decode($json);
        $data = (array)$params['data'];
        $this->assertEquals('11', $data['product']);
        $this->assertArrayHasKey('uenc', $data);
        $this->assertStringEndsWith('wishlist/index/add/', $params['action']);
    }

    public function testGetMoveFromCartParams()
    {
        $json = $this->_wishlistHelper->getMoveFromCartParams(11);
        $params = (array)json_decode($json);
        $data = (array)$params['data'];
        $this->assertEquals('11', $data['item']);
        $this->assertArrayHasKey('uenc', $data);
        $this->assertStringEndsWith('wishlist/index/fromcart/', $params['action']);
    }

    public function testGetUpdateParams()
    {
        $product = $this->objectManager->get('Magento\Catalog\Model\Product');
        $product->setId(11);
        $product->setWishlistItemId(15);
        $json = $this->_wishlistHelper->getUpdateParams($product);
        $params = (array)json_decode($json);
        $data = (array)$params['data'];
        $this->assertEquals('11', $data['product']);
        $this->assertEquals('15', $data['id']);
        $this->assertArrayHasKey('uenc', $data);
        $this->assertStringEndsWith('wishlist/index/updateItemOptions/', $params['action']);
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testWishlistCustomer()
    {
        /** @var \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository */
        $customerRepository = $this->objectManager->create(
            'Magento\Customer\Api\CustomerRepositoryInterface'
        );
        $customer = $customerRepository->getById(1);

        $this->_wishlistHelper->setCustomer($customer);
        $this->assertSame($customer, $this->_wishlistHelper->getCustomer());

        $this->_wishlistHelper = null;
        /** @var \Magento\Wishlist\Helper\Data wishlistHelper */
        $this->_wishlistHelper = $this->objectManager->get('Magento\Wishlist\Helper\Data');

        $this->_customerSession->loginById(1);
        $this->assertEquals($customer, $this->_wishlistHelper->getCustomer());

        /** @var \Magento\Customer\Helper\View $customerViewHelper */
        $customerViewHelper = $this->objectManager->create('Magento\Customer\Helper\View');
        $this->assertEquals($customerViewHelper->getCustomerName($customer), $this->_wishlistHelper->getCustomerName());
    }
}
