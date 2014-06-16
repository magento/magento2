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
namespace Magento\Sales\Controller\Adminhtml\Order;

/**
 * @magentoAppArea adminhtml
 */
class CreditmemoTest extends \Magento\Backend\Utility\Controller
{
    /**
     * @magentoConfigFixture current_store cataloginventory/item_options/auto_return 1
     * @magentoDataFixture Magento/Sales/_files/order_info.php
     */
    public function testAddCommentAction()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var \Magento\CatalogInventory\Model\Stock\Status $status */
        $status = $objectManager->get('Magento\CatalogInventory\Model\Stock\Status');
        $status->updateStatus(1);
        /** @var \Magento\CatalogInventory\Model\Stock\Item $stockItem */
        $stockItem = $objectManager->create('Magento\CatalogInventory\Model\Stock\Item');
        $stockItem->loadByProduct(1);
        $this->assertEquals(95, $stockItem->getStockQty());
        $stockItem = null;

        /** @var \Magento\Sales\Model\Order $order */
        $order = $objectManager->create('Magento\Sales\Model\Order');
        $order->load('100000001', 'increment_id');

        $items = $order->getCreditmemosCollection()->getItems();
        $creditmemo = array_shift($items);
        $comment = 'Test Comment 02';

        $this->getRequest()->setParam('creditmemo_id', $creditmemo->getId());
        $this->getRequest()->setPost('comment', array('comment' => $comment));
        $this->dispatch('backend/sales/order_creditmemo/addComment/id/' . $creditmemo->getId());

        $html = $this->getResponse()->getBody();

        $this->assertContains($comment, $html);
        /** @var \Magento\CatalogInventory\Model\Stock\Item $stockItem */
        $stockItem = $objectManager->create('Magento\CatalogInventory\Model\Stock\Item');
        $stockItem->loadByProduct(1);
        $this->assertEquals(95, $stockItem->getStockQty());
    }
}
