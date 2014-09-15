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
namespace Magento\Rss\Controller;

class CatalogTest extends \Magento\TestFramework\TestCase\AbstractController
{
    /**
     * @param string $action
     * @dataProvider actionNoFeedDataProvider
     */
    public function testActionsNoFeed($action)
    {
        $this->dispatch("rss/catalog/{$action}");
        $this->assertHeaderPcre('Http/1.1', '/^404 Not Found$/');
        $this->assertEquals('There was no RSS feed enabled.', $this->getResponse()->getBody());
    }

    /**
     * @return array
     */
    public function actionNoFeedDataProvider()
    {
        return array(array('new'), array('special'), array('salesrule'), array('category'));
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/products_new.php
     * @magentoConfigFixture current_store rss/catalog/new 1
     */
    public function testNewAction()
    {
        $this->dispatch('rss/catalog/new');
        $this->assertContains('New Product', $this->getResponse()->getBody());
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_special_price.php
     * @magentoConfigFixture current_store rss/catalog/special 1
     */
    public function testSpecialAction()
    {
        $this->dispatch('rss/catalog/special');
        $body = $this->getResponse()->getBody();
        $this->assertContains('$10.00', $body);
        $this->assertContains('$5.99', $body);
    }

    /**
     * @magentoConfigFixture current_store rss/catalog/salesrule 1
     */
    public function testSalesruleAction()
    {
        $this->dispatch('rss/catalog/salesrule');
        $this->assertHeaderPcre('Content-Type', '/text\/xml/');
        // to improve accuracy of the test, implement a fixture of a shopping cart price rule with a coupon
        $this->assertContains(
            '<link>http://localhost/index.php/rss/catalog/salesrule/</link>',
            $this->getResponse()->getBody()
        );
    }

    /**
     * @dataProvider authorizationFailedDataProvider
     * @magentoAppArea adminhtml
     */
    public function testAuthorizationFailed($action)
    {
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Backend\Model\UrlInterface'
        )->turnOffSecretKey();
        $this->dispatch("backend/rss/catalog/{$action}");
        $this->assertHeaderPcre('Http/1.1', '/^401 Unauthorized$/');
    }

    /**
     * @return array
     */
    public function authorizationFailedDataProvider()
    {
        return array(array('notifystock'), array('review'));
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/multiple_products.php
     * @magentoConfigFixture admin_store cataloginventory/item_options/notify_stock_qty 75
     * @magentoConfigFixture current_store cataloginventory/item_options/notify_stock_qty 75
     */
    public function testNotifyStockAction()
    {
        // workaround: trigger updating "low stock date", because RSS collection requires it to be not null
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\CatalogInventory\Model\Resource\Stock'
        )->updateLowStockDate();
        $this->_loginAdmin();
        $this->dispatch('backend/rss/catalog/notifystock');

        $this->assertHeaderPcre('Content-Type', '/text\/xml/');

        // assert that among 2 products in fixture, there is only one with 50 qty
        $body = $this->getResponse()->getBody();
        $this->assertNotContains('<![CDATA[Simple Product]]>', $body);
        // this one was supposed to have qty 100 ( > 75)
        $this->assertContains('<![CDATA[Simple Product2]]>', $body);
        // 50 < 75
        // this one was supposed to have qty 140 ( > 75)
        $this->assertNotContains('<![CDATA[Simple Product 3]]>', $body);
    }

    /**
     * @magentoDataFixture Magento/Review/_files/reviews.php
     */
    public function testReviewAction()
    {
        $this->_loginAdmin();
        $this->dispatch('backend/rss/catalog/review');
        $this->assertHeaderPcre('Content-Type', '/text\/xml/');
        $body = $this->getResponse()->getBody();
        $this->assertContains('"Simple Product 3"', $body);
        $this->assertContains('Review text', $body);
    }

    /**
     * @magentoConfigFixture current_store rss/catalog/category 1
     */
    public function testCategoryAction()
    {
        $this->getRequest()->setParam(
            'cid',
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
                'Magento\Framework\StoreManagerInterface'
            )->getStore()->getRootCategoryId()
        );
        $this->dispatch('rss/catalog/category');
        $this->assertStringMatchesFormat(
            '%A<link>http://localhost/index.php/catalog/category/view/%A/id/2/</link>%A',
            $this->getResponse()->getBody()
        );
    }

    /**
     * Emulate administrator logging in
     */
    protected function _loginAdmin()
    {
        \Magento\TestFramework\Helper\Bootstrap::getInstance()->loadArea(
            \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE
        );
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\DesignInterface'
        )->setDefaultDesignTheme();
        $this->getRequest()->setServer(
            array(
                'PHP_AUTH_USER' => \Magento\TestFramework\Bootstrap::ADMIN_NAME,
                'PHP_AUTH_PW' => \Magento\TestFramework\Bootstrap::ADMIN_PASSWORD
            )
        );
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Backend\Model\UrlInterface'
        )->turnOffSecretKey();
    }
}
