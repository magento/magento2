<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Block\Adminhtml\Edit\Tab;


/**
 * Magento\Customer\Block\Adminhtml\Edit\Tab\Carts
 *
 * @magentoAppArea adminhtml
 */
class CartsTest extends \PHPUnit_Framework_TestCase
{
    /** @var Carts */
    private $_block;

    /** @var \Magento\Customer\Api\CustomerRepositoryInterface */
    private $_customerRepository;

    /** @var \Magento\Backend\Block\Template\Context */
    private $_context;

    /** @var \Magento\Framework\ObjectManagerInterface */
    private $_objectManager;

    public function setUp()
    {
        $this->_objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->_customerRepository = $this->_objectManager->get(
            'Magento\Customer\Api\CustomerRepositoryInterface'
        );
        $storeManager = $this->_objectManager->get('Magento\Store\Model\StoreManager');
        $this->_context = $this->_objectManager->get(
            'Magento\Backend\Block\Template\Context',
            ['storeManager' => $storeManager]
        );
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testGetHtml()
    {
        $customer = $this->_customerRepository->getById(1);
        $data = ['account' => $customer->__toArray()];
        $this->_context->getBackendSession()->setCustomerData($data);

        $this->_block = $this->_objectManager->get(
            'Magento\Framework\View\LayoutInterface'
        )->createBlock(
            'Magento\Customer\Block\Adminhtml\Edit\Tab\Carts',
            '',
            ['context' => $this->_context]
        );

        $html = $this->_block->toHtml();
        $this->assertContains("<div id=\"customer_cart_grid1\"", $html);
        $this->assertRegExp(
            '/<div class=".*admin__data-grid-toolbar"/',
            $html
        );
        $this->assertContains("customer_cart_grid1JsObject = new varienGrid(\"customer_cart_grid1\",", $html);
        $this->assertContains("backend/customer/cart_product_composite_cart/configure/website_id/1", $html);
    }

    public function testGetHtmlNoCustomer()
    {
        $data = ['account' => []];
        $this->_context->getBackendSession()->setCustomerData($data);

        $this->_block = $this->_objectManager->get(
            'Magento\Framework\View\LayoutInterface'
        )->createBlock(
            'Magento\Customer\Block\Adminhtml\Edit\Tab\Carts',
            '',
            ['context' => $this->_context]
        );

        $html = $this->_block->toHtml();
        $this->assertContains("<div id=\"customer_cart_grid\"", $html);
        $this->assertRegExp(
            '/<div class=".*admin__data-grid-toolbar"/',
            $html
        );
        $this->assertContains("customer_cart_gridJsObject = new varienGrid(\"customer_cart_grid\",", $html);
        $this->assertContains("backend/customer/cart_product_composite_cart/configure/key/", $html);
    }
}
