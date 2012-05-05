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
 * @package     Mage_DesignEditor
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test for presence of the design editor toolbar on frontend pages
 *
 * @magentoDataFixture Mage/DesignEditor/_files/design_editor_active.php
 * @magentoDataFixture Mage/Catalog/controllers/_files/products.php
 */
class Mage_DesignEditor_Block_ToolbarCrosscuttingTest extends Magento_Test_TestCase_ControllerAbstract
{
    /**
     * Assert that a page content contains the design editor toolbar
     *
     * @param string $content
     */
    protected function _assertContainsToolbar($content)
    {
        $this->assertContains('id="vde_toolbar"', $content);
    }

    public function testCmsHomePage()
    {
        $this->dispatch('cms/index/index');
        $this->_assertContainsToolbar($this->getResponse()->getBody());
    }

    public function testCustomerAccountLogin()
    {
        $this->dispatch('customer/account/login');
        $this->_assertContainsToolbar($this->getResponse()->getBody());
    }

    public function testCatalogProductView()
    {
        $this->dispatch('catalog/product/view/id/1');
        $this->_assertContainsToolbar($this->getResponse()->getBody());
    }

    public function testCheckoutCart()
    {
        $this->dispatch('checkout/cart/index');
        $this->_assertContainsToolbar($this->getResponse()->getBody());
    }
}
