<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Wishlist\Block\Share;

class WishlistTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $_objectManager;

    /**
     * @var \Magento\Wishlist\Block\Share\Wishlist
     */
    protected $_block;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    public function setUp()
    {
        $this->_objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->_customerSession = $this->_objectManager->get(\Magento\Customer\Model\Session::class);
        $this->_block = $this->_objectManager->create(\Magento\Wishlist\Block\Share\Wishlist::class);
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Wishlist/_files/wishlist.php
     */
    public function testGetWishlistCustomer()
    {
        $this->_customerSession->loginById(1);
        $expectedCustomer = $this->_customerSession->getCustomerDataObject();
        $actualCustomer = $this->_block->getWishlistCustomer();
        $this->assertInstanceOf(\Magento\Customer\Api\Data\CustomerInterface::class, $actualCustomer);
        $this->assertSame((int)$expectedCustomer->getId(), (int)$actualCustomer->getId());
        $this->assertSame((int)$expectedCustomer->getWebsiteId(), (int)$actualCustomer->getWebsiteId());
        $this->assertSame((int)$expectedCustomer->getStoreId(), (int)$actualCustomer->getStoreId());
        $this->assertSame((int)$expectedCustomer->getGroupId(), (int)$actualCustomer->getGroupId());
        $this->assertSame($expectedCustomer->getCustomAttributes(), $actualCustomer->getCustomAttributes());
        $this->assertSame($expectedCustomer->getFirstname(), $actualCustomer->getFirstname());
        $this->assertSame($expectedCustomer->getLastname(), $actualCustomer->getLastname());
        $this->assertSame($expectedCustomer->getEmail(), $actualCustomer->getEmail());
        $this->assertSame($expectedCustomer->getEmail(), $actualCustomer->getEmail());
        $this->assertSame((int)$expectedCustomer->getDefaultBilling(), (int)$actualCustomer->getDefaultBilling());
        $this->assertSame((int)$expectedCustomer->getDefaultShipping(), (int)$actualCustomer->getDefaultShipping());
    }
}
