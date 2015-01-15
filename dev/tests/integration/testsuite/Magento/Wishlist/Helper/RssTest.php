<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Wishlist\Helper;

class RssTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * Core data
     *
     * @var \Magento\Framework\Url\EncoderInterface
     */
    protected $urlEncoder;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Framework\App\Helper\Context
     */
    protected $_contextHelper;

    /**
     * @var \Magento\Wishlist\Helper\Rss
     */
    protected $_wishlistHelper;

    /**
     * @var int
     */
    protected $_fixtureCustomerId;

    protected function setUp()
    {
        $this->_fixtureCustomerId = 1;

        $this->_objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->_customerSession = $this->_objectManager->create('Magento\Customer\Model\Session');
        $this->urlEncoder = $this->_objectManager->create('Magento\Framework\Url\EncoderInterface');

        $this->_contextHelper = $this->_objectManager->create('Magento\Framework\App\Helper\Context');
        $request = $this->_contextHelper->getRequest();
        $request->setParam('data', $this->urlEncoder->encode($this->_fixtureCustomerId));

        $this->_wishlistHelper = $this->_objectManager->create('Magento\Wishlist\Helper\Rss',
            [
                'context' => $this->_contextHelper,
                'customerSession' => $this->_customerSession
            ]
        );

        $this->_customerSession->loginById($this->_fixtureCustomerId);
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoAppArea frontend
     */
    public function testGetCustomer()
    {
        $expectedCustomer = $this->_customerSession->getCustomerDataObject();
        $actualCustomer = $this->_wishlistHelper->getCustomer();
        $this->assertInstanceOf('Magento\Customer\Api\Data\CustomerInterface', $actualCustomer);
        $this->assertEquals((int)$expectedCustomer->getId(), (int)$actualCustomer->getId());
        $this->assertEquals((int)$expectedCustomer->getWebsiteId(), (int)$actualCustomer->getWebsiteId());
        $this->assertEquals((int)$expectedCustomer->getStoreId(), (int)$actualCustomer->getStoreId());
        $this->assertEquals((int)$expectedCustomer->getGroupId(), (int)$actualCustomer->getGroupId());
        $this->assertEquals($expectedCustomer->getCustomAttributes(), $actualCustomer->getCustomAttributes());
        $this->assertEquals($expectedCustomer->getFirstname(), $actualCustomer->getFirstname());
        $this->assertEquals($expectedCustomer->getLastname(), $actualCustomer->getLastname());
        $this->assertEquals($expectedCustomer->getEmail(), $actualCustomer->getEmail());
        $this->assertEquals($expectedCustomer->getEmail(), $actualCustomer->getEmail());
        $this->assertEquals((int)$expectedCustomer->getDefaultBilling(), (int)$actualCustomer->getDefaultBilling());
        $this->assertEquals((int)$expectedCustomer->getDefaultShipping(), (int)$actualCustomer->getDefaultShipping());
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Wishlist/_files/wishlist_with_product_qty_increments.php
     * @magentoAppArea frontend
     */
    public function testGetWishlistByParam()
    {
        /** @var \Magento\Wishlist\Model\Wishlist $wishlist */
        $wishlist = $this->_objectManager->create('Magento\Wishlist\Model\Wishlist')
            ->loadByCustomerId($this->_fixtureCustomerId);
        $wishlist->load($wishlist->getId());

        /** @var \Magento\Framework\App\Request\Http $request */
        $request = $this->_contextHelper->getRequest();
        $request->setParam('wishlist_id', $wishlist->getId());

        $this->assertEquals($wishlist, $this->_wishlistHelper->getWishlist());
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Wishlist/_files/wishlist_with_product_qty_increments.php
     * @magentoAppArea frontend
     */
    public function testGetWishlistByCustomerId()
    {
        /** @var \Magento\Wishlist\Model\Wishlist $wishlist */
        $wishlist = $this->_objectManager->create('Magento\Wishlist\Model\Wishlist')
            ->loadByCustomerId($this->_fixtureCustomerId);

        /** @var \Magento\Framework\App\Request\Http $request */
        $request = $this->_contextHelper->getRequest();
        $request->setParam('wishlist_id', '');

        $this->assertEquals($wishlist, $this->_wishlistHelper->getWishlist());
    }
}
