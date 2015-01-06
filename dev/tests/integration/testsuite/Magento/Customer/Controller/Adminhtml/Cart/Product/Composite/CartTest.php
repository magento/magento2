<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Customer\Controller\Adminhtml\Cart\Product\Composite;

/**
 * @magentoAppArea adminhtml
 */
class CartTest extends \Magento\Backend\Utility\Controller
{
    /**
     * @var \Magento\Sales\Model\Resource\Quote\Item\CollectionFactory
     */
    protected $quoteItemCollectionFactory;

    public function setUp()
    {
        parent::setUp();
        $this->quoteItemCollectionFactory = $this->_objectManager->get(
            'Magento\Sales\Model\Resource\Quote\Item\CollectionFactory'
        );
    }

    public function testConfigureActionNoCustomerId()
    {
        $this->dispatch('backend/customer/cart_product_composite_cart/configure');
        $this->assertEquals('{"error":true,"message":"No customer ID defined."}', $this->getResponse()->getBody());
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testConfigureActionNoQuoteId()
    {
        $this->getRequest()->setParam('customer_id', 1);
        $this->getRequest()->setParam('website_id', 1);
        $this->dispatch('backend/customer/cart_product_composite_cart/configure');
        $this->assertEquals(
            '{"error":true,"message":"Please correct the quote items and try again."}',
            $this->getResponse()->getBody()
        );
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/quote.php
     */
    public function testConfigureAction()
    {
        $items = $this->quoteItemCollectionFactory->create();
        $itemId = $items->getAllIds()[0];
        $this->getRequest()->setParam('customer_id', 1);
        $this->getRequest()->setParam('website_id', 1);
        $this->getRequest()->setParam('id', $itemId);
        $this->dispatch('backend/customer/cart_product_composite_cart/configure');
        $this->assertContains(
            '<input id="product_composite_configure_input_qty" class="input-text" type="text" name="qty" value="1">',
            $this->getResponse()->getBody()
        );
    }

    public function testUpdateActionNoCustomerId()
    {
        $this->dispatch('backend/customer/cart_product_composite_cart/update');
        $this->assertRedirect($this->stringContains('catalog/product/showUpdateResult'));
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testUpdateActionNoQuoteId()
    {
        $this->getRequest()->setParam('customer_id', 1);
        $this->getRequest()->setParam('website_id', 1);
        $this->dispatch('backend/customer/cart_product_composite_cart/update');
        $this->assertRedirect($this->stringContains('catalog/product/showUpdateResult'));
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/quote.php
     */
    public function testUpdateAction()
    {
        $items = $this->quoteItemCollectionFactory->create();
        $itemId = $items->getAllIds()[0];
        $this->getRequest()->setParam('customer_id', 1);
        $this->getRequest()->setParam('website_id', 1);
        $this->getRequest()->setParam('id', $itemId);

        $this->dispatch('backend/customer/cart_product_composite_cart/update');
        $this->assertRedirect($this->stringContains('catalog/product/showUpdateResult'));
    }
}
