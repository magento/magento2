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
 * @category    Magento
 * @package     Magento_Rss
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Rss\Controller;

class IndexTest extends \Magento\TestFramework\TestCase\AbstractController
{
    public function testIndexActionDisabled()
    {
        $this->dispatch('rss/index/index');
        $this->assert404NotFound();
    }

    /**
     * @magentoConfigFixture current_store rss/config/active 1
     * @magentoConfigFixture current_store rss/catalog/new 1
     */
    public function testIndexAction()
    {
        $this->dispatch('rss/index/index');
        $this->assertContains('/rss/catalog/new/', $this->getResponse()->getBody());
    }

    public function testNofeedAction()
    {
        $this->dispatch('rss/index/nofeed');
        $this->assertHeaderPcre('Status', '/404/');
        $this->assertHeaderPcre('Content-Type', '/text\/plain/');
    }

    /**
     * @magentoConfigFixture current_store rss/wishlist/active 1
     * @magentoDataFixture Magento/Wishlist/_files/wishlist.php
     * @magentoAppIsolation enabled
     */
    public function testWishlistAction()
    {
        $wishlist = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Wishlist\Model\Wishlist');
        $wishlist->load('fixture_unique_code', 'sharing_code');
        $this->getRequest()->setParam('wishlist_id', $wishlist->getId())
            ->setParam('data', base64_encode('1'))
        ;
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Customer\Model\Session')
            ->login('customer@example.com', 'password');
        $this->dispatch('rss/index/wishlist');
        $this->assertContains('<![CDATA[Simple Product]]>', $this->getResponse()->getBody());
    }
}
