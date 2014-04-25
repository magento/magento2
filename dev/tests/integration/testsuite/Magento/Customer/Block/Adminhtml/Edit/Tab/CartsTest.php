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
namespace Magento\Customer\Block\Adminhtml\Edit\Tab;

use Magento\Customer\Controller\Adminhtml\Index;

/**
 * Magento\Customer\Block\Adminhtml\Edit\Tab\Carts
 *
 * @magentoAppArea adminhtml
 */
class CartsTest extends \PHPUnit_Framework_TestCase
{
    /** @var Carts */
    private $_block;

    /** @var \Magento\Customer\Service\V1\CustomerAccountServiceInterface */
    private $_customerAccountService;

    /** @var \Magento\Backend\Block\Template\Context */
    private $_context;

    /** @var \Magento\Framework\ObjectManager */
    private $_objectManager;

    public function setUp()
    {
        $this->_objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->_customerAccountService = $this->_objectManager->get(
            'Magento\Customer\Service\V1\CustomerAccountServiceInterface'
        );
        $storeManager = $this->_objectManager->get('Magento\Store\Model\StoreManager');
        $this->_context = $this->_objectManager->get(
            'Magento\Backend\Block\Template\Context',
            array('storeManager' => $storeManager)
        );
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testGetHtml()
    {
        $customer = $this->_customerAccountService->getCustomer(1);
        $data = array('account' => $customer->__toArray());
        $this->_context->getBackendSession()->setCustomerData($data);

        $this->_block = $this->_objectManager->get(
            'Magento\Framework\View\LayoutInterface'
        )->createBlock(
            'Magento\Customer\Block\Adminhtml\Edit\Tab\Carts',
            '',
            array('context' => $this->_context)
        );

        $html = $this->_block->toHtml();
        $this->assertContains("<div id=\"customer_cart_grid1\">", $html);
        $this->assertContains("<div class=\"grid-actions\">", $html);
        $this->assertContains("customer_cart_grid1JsObject = new varienGrid('customer_cart_grid1',", $html);
        $this->assertContains("backend/customer/cart_product_composite_cart/configure/website_id/1", $html);
    }

    public function testGetHtmlNoCustomer()
    {
        $data = array('account' => array());
        $this->_context->getBackendSession()->setCustomerData($data);

        $this->_block = $this->_objectManager->get(
            'Magento\Framework\View\LayoutInterface'
        )->createBlock(
            'Magento\Customer\Block\Adminhtml\Edit\Tab\Carts',
            '',
            array('context' => $this->_context)
        );

        $html = $this->_block->toHtml();
        $this->assertContains("<div id=\"customer_cart_grid0\">", $html);
        $this->assertContains("<div class=\"grid-actions\">", $html);
        $this->assertContains("customer_cart_grid0JsObject = new varienGrid('customer_cart_grid0',", $html);
        $this->assertContains("backend/customer/cart_product_composite_cart/configure/website_id/0/key/", $html);
    }
}
