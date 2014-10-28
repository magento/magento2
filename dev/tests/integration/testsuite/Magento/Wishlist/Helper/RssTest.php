<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
     * @var \Magento\Core\Helper\Data
     */
    protected $_coreData;

    /**
     * @var \Magento\Framework\ObjectManager
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
        $this->_coreData = $this->_objectManager->create('Magento\Core\Helper\Data');

        $this->_contextHelper = $this->_objectManager->create('Magento\Framework\App\Helper\Context');
        $request = $this->_contextHelper->getRequest();
        $request->setParam('data', $this->_coreData->urlEncode($this->_fixtureCustomerId));

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
        $this->assertEquals($this->_customerSession->getCustomerDataObject(), $this->_wishlistHelper->getCustomer());
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
