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
 * @package     Mage_Rss
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Rss_CatalogControllerTest extends Magento_Test_TestCase_ControllerAbstract
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
        return array(array('new'), array('special'), array('salesrule'), array('tag'), array('category'));
    }

    /**
     * @magentoDataFixture Mage/Catalog/_files/products_new.php
     * @magentoConfigFixture current_store rss/catalog/new 1
     */
    public function testNewAction()
    {
        $this->dispatch('rss/catalog/new');
        $this->assertContains('New Product', $this->getResponse()->getBody());
    }

    /**
     * @magentoDataFixture Mage/Catalog/_files/product_special_price.php
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
            '<link>http://localhost/index.php/rss/catalog/salesrule/</link>', $this->getResponse()->getBody()
        );
    }

    /**
     * @magentoConfigFixture current_store rss/catalog/tag 1
     */
    public function testTagAction()
    {
        $this->dispatch('rss/catalog/tag');
        // this test is also inaccurate without a fixture of product with tags
        $this->assertEquals('nofeed', $this->getRequest()->getActionName());
        $this->assertHeaderPcre('Status', '/^404 File not found$/');
    }

    /**
     * @dataProvider authorizationFailedDataProvider
     */
    public function testAuthorizationFailed($action)
    {
        $this->dispatch("rss/catalog/{$action}");
        $this->assertHeaderPcre('Http/1.1', '/^401 Unauthorized$/');
    }

    /**
     * @return array
     */
    public function authorizationFailedDataProvider()
    {
        return array(
            array('notifystock'),
            array('review')
        );
    }

    /**
     * @magentoDataFixture Mage/Catalog/_files/two_products.php
     * @magentoConfigFixture current_store cataloginventory/item_options/notify_stock_qty 75
     */
    public function testNotifyStockAction()
    {
        // workaround: trigger updating "low stock date", because RSS collection requires it to be not null
        Mage::getResourceSingleton('Mage_CatalogInventory_Model_Resource_Stock')->updateLowStockDate();
        $this->_loginAdmin();
        $this->dispatch('rss/catalog/notifystock');

        $this->assertHeaderPcre('Content-Type', '/text\/xml/');

        // assert that among 2 products in fixture, there is only one with 50 qty
        $body = $this->getResponse()->getBody();
        $this->assertNotContains('<![CDATA[Simple Product]]>', $body); // this one was supposed to have qty 100 ( > 75)
        $this->assertContains('<![CDATA[Simple Product2]]>', $body); // 50 < 75
    }

    /**
     * @magentoDataFixture Mage/Review/_files/reviews.php
     */
    public function testReviewAction()
    {
        $this->_loginAdmin();
        $this->dispatch('rss/catalog/review');
        $this->assertHeaderPcre('Content-Type', '/text\/xml/');
        $body = $this->getResponse()->getBody();
        $this->assertContains('"Simple Product2"', $body);
        $this->assertContains('Review text', $body);
    }

    /**
     * @magentoConfigFixture current_store rss/catalog/category 1
     */
    public function testCategoryAction()
    {
        $this->getRequest()->setParam('cid', Mage::app()->getStore()->getRootCategoryId());
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
        $this->getRequest()->setServer(array(
            'PHP_AUTH_USER' => Magento_Test_Bootstrap::ADMIN_NAME,
            'PHP_AUTH_PW' => Magento_Test_Bootstrap::ADMIN_PASSWORD
        ));
    }
}
