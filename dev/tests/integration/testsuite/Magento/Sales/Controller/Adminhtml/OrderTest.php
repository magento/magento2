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
        $prohibitedStrings = array('validate-vat', 'validateVat', 'Validate VAT');
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

        $this->getRequest()->setPost(array('history' => array('status' => $status, 'comment' => $comment)));
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
        return array(
            array('status' => 'pending', 'comment' => 'Test comment', 'response' => 'Test comment'),
            array(
                'status' => 'processing',
                'comment' => '',
                'response' => '{"error":true,"message":"Comment text cannot be empty."}'
            )
        );
    }
}
