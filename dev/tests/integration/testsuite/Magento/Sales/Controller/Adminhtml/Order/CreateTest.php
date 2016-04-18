<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml\Order;

/**
 * @magentoAppArea adminhtml
 * @magentoDbIsolation enabled
 */
class CreateTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    public function testLoadBlockAction()
    {
        $this->getRequest()->setParam('block', ',');
        $this->getRequest()->setParam('json', 1);
        $this->dispatch('backend/sales/order_create/loadBlock');
        $this->assertEquals('{"message":""}', $this->getResponse()->getBody());
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testLoadBlockActionData()
    {
        $this->_objectManager->get(
            'Magento\Sales\Model\AdminOrder\Create'
        )->addProducts(
            [1 => ['qty' => 1]]
        );
        $this->getRequest()->setParam('block', 'data');
        $this->getRequest()->setParam('json', 1);
        $this->dispatch('backend/sales/order_create/loadBlock');
        $html = $this->getResponse()->getBody();
        $this->assertContains('<div id=\"sales_order_create_search_grid\"', $html);
        $this->assertContains('<div id=\"order-billing_method_form\"', $html);
        $this->assertContains('id=\"shipping-method-overlay\"', $html);
        $this->assertContains('id=\"coupons:code\"', $html);
    }

    /**
     * @dataProvider loadBlockActionsDataProvider
     */
    public function testLoadBlockActions($block, $expected)
    {
        $this->getRequest()->setParam('block', $block);
        $this->getRequest()->setParam('json', 1);
        $this->dispatch('backend/sales/order_create/loadBlock');
        $html = $this->getResponse()->getBody();
        $this->assertContains($expected, $html);
    }

    public function loadBlockActionsDataProvider()
    {
        return [
            'shipping_method' => ['shipping_method', 'id=\"shipping-method-overlay\"'],
            'billing_method' => ['billing_method', '<div id=\"order-billing_method_form\">'],
            'newsletter' => ['newsletter', 'name=\"newsletter:subscribe\"'],
            'search' => ['search', '<div id=\"sales_order_create_search_grid\"'],
            'search_grid' => ['search', '<div id=\"sales_order_create_search_grid\"']
        ];
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testLoadBlockActionItems()
    {
        $this->_objectManager->get(
            'Magento\Sales\Model\AdminOrder\Create'
        )->addProducts(
            [1 => ['qty' => 1]]
        );
        $this->getRequest()->setParam('block', 'items');
        $this->getRequest()->setParam('json', 1);
        $this->dispatch('backend/sales/order_create/loadBlock');
        $html = $this->getResponse()->getBody();
        $this->assertContains('id=\"coupons:code\"', $html);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoAppArea adminhtml
     */
    public function testIndexAction()
    {
        /** @var $order \Magento\Sales\Model\AdminOrder\Create */
        $order = $this->_objectManager->get('Magento\Sales\Model\AdminOrder\Create');
        $order->addProducts([1 => ['qty' => 1]]);
        $this->dispatch('backend/sales/order_create/index');
        $html = $this->getResponse()->getBody();

        $this->assertSelectCount('div#order-customer-selector', true, $html);
        $this->assertSelectCount('[data-grid-id=sales_order_create_customer_grid]', true, $html);
        $this->assertSelectCount('div#order-billing_method_form', true, $html);
        $this->assertSelectCount('#shipping-method-overlay', true, $html);
        $this->assertSelectCount('div#sales_order_create_search_grid', true, $html);
        $this->assertSelectCount('#coupons:code', true, $html);
    }

    /**
     * @param string $actionName
     * @param boolean $reordered
     * @param string $expectedResult
     *
     * @dataProvider getAclResourceDataProvider
     * @magentoAppIsolation enabled
     */
    public function testGetAclResource($actionName, $reordered, $expectedResult)
    {
        $this->_objectManager->get('Magento\Backend\Model\Session\Quote')->setReordered($reordered);
        $orderController = $this->_objectManager->get(
            'Magento\Sales\Controller\Adminhtml\Order\Stub\OrderCreateStub'
        );

        $this->getRequest()->setActionName($actionName);

        $method = new \ReflectionMethod('\Magento\Sales\Controller\Adminhtml\Order\Create', '_getAclResource');
        $method->setAccessible(true);
        $result = $method->invoke($orderController);
        $this->assertEquals($result, $expectedResult);
    }

    /**
     * @return array
     */
    public function getAclResourceDataProvider()
    {
        return [
            ['index', false, 'Magento_Sales::create'],
            ['index', true, 'Magento_Sales::reorder'],
            ['save', false, 'Magento_Sales::create'],
            ['save', true, 'Magento_Sales::reorder'],
            ['reorder', false, 'Magento_Sales::reorder'],
            ['reorder', true, 'Magento_Sales::reorder'],
            ['cancel', false, 'Magento_Sales::cancel'],
            ['cancel', true, 'Magento_Sales::reorder'],
            ['', false, 'Magento_Sales::actions'],
            ['', true, 'Magento_Sales::actions']
        ];
    }

    /**
     * @magentoDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     * @magentoAppArea adminhtml
     */
    public function testConfigureProductToAddAction()
    {
        $this->getRequest()->setParam('id', 1)
            ->setParam('isAjax', true);

        $this->dispatch('backend/sales/order_create/configureProductToAdd');

        $body = $this->getResponse()->getBody();

        $this->assertNotEmpty($body);
        $this->assertContains('><span>Quantity</span></label>', $body);
        $this->assertContains('>Test Configurable</label>', $body);
        $this->assertContains('"code":"test_configurable","label":"Test Configurable"', $body);
        $this->assertContains('"productId":"1"', $body);
    }

    public function testDeniedSaveAction()
    {
        $this->_objectManager->configure(
            [
                'Magento\Backend\App\Action\Context' => [
                    'arguments' => [
                        'authorization' => [
                            'instance' => 'Magento\Sales\Controller\Adminhtml\Order\AuthorizationMock',
                        ],
                    ],
                ],
            ]
        );
        \Magento\TestFramework\Helper\Bootstrap::getInstance()
            ->loadArea('adminhtml');

        $this->dispatch('backend/sales/order_create/save');
        $this->assertEquals('403', $this->getResponse()->getHttpResponseCode());
    }
}
