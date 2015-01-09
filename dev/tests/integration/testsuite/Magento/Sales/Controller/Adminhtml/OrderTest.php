<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml;

/**
 * @magentoAppArea adminhtml
 */
class OrderTest extends \Magento\Backend\Utility\Controller
{
    public function testIndexAction()
    {
        $this->dispatch('backend/sales/order/index');
        $this->assertContains('Total 0 records found', $this->getResponse()->getBody());
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testIndexActionWithOrder()
    {
        $this->dispatch('backend/sales/order/index');
        $this->assertContains('Total 1 records found', $this->getResponse()->getBody());
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testOrderViewAction()
    {
        /** @var $order \Magento\Sales\Model\Order */
        $order = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Sales\Model\Order');
        $order->load('100000001', 'increment_id');
        $this->dispatch('backend/sales/order/view/order_id/' . $order->getId());
        $this->assertContains('Los Angeles', $this->getResponse()->getBody());
    }

    public function testAddressActionNonExistingAddress()
    {
        $this->getRequest()->setParam('address_id', -1);
        $this->dispatch('backend/sales/order/address');
        $this->assertRedirect();
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/address.php
     */
    public function testAddressActionNoVAT()
    {
        /** @var $address \Magento\Sales\Model\Order\Address */
        $address = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Sales\Model\Order\Address'
        );
        $address->load('a_unique_firstname', 'firstname');
        $this->getRequest()->setParam('address_id', $address->getId());
        $this->dispatch('backend/sales/order/address');
        $html = $this->getResponse()->getBody();
        $prohibitedStrings = ['validate-vat', 'validateVat', 'Validate VAT'];
        foreach ($prohibitedStrings as $string) {
            $this->assertNotContains($string, $html, 'VAT button must not be shown while editing address', true);
        }
    }

    /**
     * Test add comment to order
     *
     * @param $status
     * @param $comment
     * @param $response
     * @magentoDataFixture Magento/Sales/_files/order.php
     * @dataProvider getAddCommentData
     */
    public function testAddCommentAction($status, $comment, $response)
    {
        /** @var $order \Magento\Sales\Model\Order */
        $order = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Sales\Model\Order');
        $order->load('100000001', 'increment_id');

        $this->getRequest()->setPost(['history' => ['status' => $status, 'comment' => $comment]]);
        $this->dispatch('backend/sales/order/addComment/order_id/' . $order->getId());
        $html = $this->getResponse()->getBody();

        $this->assertContains($response, $html);
    }

    /**
     * Get Add Comment Data
     *
     * @return array
     */
    public function getAddCommentData()
    {
        return [
            ['status' => 'pending', 'comment' => 'Test comment', 'response' => 'Test comment'],
            [
                'status' => 'processing',
                'comment' => '',
                'response' => '{"error":true,"message":"Comment text cannot be empty."}'
            ]
        ];
    }
}
