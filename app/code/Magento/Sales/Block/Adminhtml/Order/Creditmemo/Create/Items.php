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
 * @package     Magento_Sales
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml creditmemo items grid
 */
namespace Magento\Sales\Block\Adminhtml\Order\Creditmemo\Create;

class Items extends \Magento\Sales\Block\Adminhtml\Items\AbstractItems
{
    protected $_canReturnToStock;

    /**
     * Sales data
     *
     * @var \Magento\Sales\Helper\Data
     */
    protected $_salesData = null;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Registry $registry
     * @param \Magento\Sales\Helper\Data $salesData
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Registry $registry,
        \Magento\Sales\Helper\Data $salesData,
        array $data = array()
    ) {
        $this->_salesData = $salesData;
        parent::__construct($context, $productFactory, $registry, $data);
    }

    /**
     * Prepare child blocks
     *
     * @return \Magento\Sales\Block\Adminhtml\Order\Creditmemo\Create\Items
     */
    protected function _prepareLayout()
    {
        $onclick = "submitAndReloadArea($('creditmemo_item_container'),'".$this->getUpdateUrl()."')";
        $this->addChild('update_button', 'Magento\Backend\Block\Widget\Button', array(
            'label'     => __('Update Qty\'s'),
            'class'     => 'update-button',
            'onclick'   => $onclick,
        ));

        if ($this->getCreditmemo()->canRefund()) {
            if ($this->getCreditmemo()->getInvoice() && $this->getCreditmemo()->getInvoice()->getTransactionId()) {
                $this->addChild('submit_button', 'Magento\Backend\Block\Widget\Button', array(
                    'label'     => __('Refund'),
                    'class'     => 'save submit-button refund',
                    'onclick'   => 'disableElements(\'submit-button\');submitCreditMemo()',
                ));
            }
            $this->addChild('submit_offline', 'Magento\Backend\Block\Widget\Button', array(
                'label'     => __('Refund Offline'),
                'class'     => 'save submit-button',
                'onclick'   => 'disableElements(\'submit-button\');submitCreditMemoOffline()',
            ));

        } else {
            $this->addChild('submit_button', 'Magento\Backend\Block\Widget\Button', array(
                'label'     => __('Refund Offline'),
                'class'     => 'save submit-button',
                'onclick'   => 'disableElements(\'submit-button\');submitCreditMemoOffline()',
            ));
        }

        return parent::_prepareLayout();
    }

    /**
     * Retrieve invoice order
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->getCreditmemo()->getOrder();
    }

    /**
     * Retrieve source
     *
     * @return \Magento\Sales\Model\Order\Creditmemo
     */
    public function getSource()
    {
        return $this->getCreditmemo();
    }

    /**
     * Retrieve order totals block settings
     *
     * @return array
     */
    public function getOrderTotalData()
    {
        return array();
    }

    /**
     * Retrieve order totalbar block data
     *
     * @return array
     */
    public function getOrderTotalbarData()
    {
        $this->setPriceDataObject($this->getOrder());

        $totalbarData = array();
        $totalbarData[] = array(__('Paid Amount'), $this->displayPriceAttribute('total_invoiced'), false);
        $totalbarData[] = array(__('Refund Amount'), $this->displayPriceAttribute('total_refunded'), false);
        $totalbarData[] = array(__('Shipping Amount'), $this->displayPriceAttribute('shipping_invoiced'), false);
        $totalbarData[] = array(__('Shipping Refund'), $this->displayPriceAttribute('shipping_refunded'), false);
        $totalbarData[] = array(__('Order Grand Total'), $this->displayPriceAttribute('grand_total'), true);
        return $totalbarData;
    }

    /**
     * Retrieve creditmemo model instance
     *
     * @return \Magento\Sales\Model\Order\Creditmemo
     */
    public function getCreditmemo()
    {
        return $this->_coreRegistry->registry('current_creditmemo');
    }

    public function canEditQty()
    {
        if ($this->getCreditmemo()->getOrder()->getPayment()->canRefund()) {
            return $this->getCreditmemo()->getOrder()->getPayment()->canRefundPartialPerInvoice();
        }
        return true;
    }

    public function getUpdateButtonHtml()
    {
        return $this->getChildHtml('update_button');
    }

    public function getUpdateUrl()
    {
        return $this->getUrl('sales/*/updateQty', array(
            'order_id' => $this->getCreditmemo()->getOrderId(),
            'invoice_id' => $this->getRequest()->getParam('invoice_id', null),
        ));
    }

    public function canReturnToStock()
    {
        $canReturnToStock = $this->_storeConfig->getConfig(
            \Magento\CatalogInventory\Model\Stock\Item::XML_PATH_CAN_SUBTRACT
        );
        if ($canReturnToStock) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Whether to show 'Return to stock' column in creaditmemo grid
     * @return bool
     */
    public function canReturnItemsToStock()
    {
        if (is_null($this->_canReturnToStock)) {
            $this->_canReturnToStock = $this->_storeConfig->getConfig(
                \Magento\CatalogInventory\Model\Stock\Item::XML_PATH_CAN_SUBTRACT
            );
            if ($this->_canReturnToStock) {
                $canReturnToStock = false;
                foreach ($this->getCreditmemo()->getAllItems() as $item) {
                    $product = $this->_productFactory->create()->load($item->getOrderItem()->getProductId());
                    if ( $product->getId() && $product->getStockItem()->getManageStock() ) {
                        $item->setCanReturnToStock($canReturnToStock = true);
                    } else {
                        $item->setCanReturnToStock(false);
                    }
                }
                $this->getCreditmemo()->getOrder()->setCanReturnToStock($this->_canReturnToStock = $canReturnToStock);
            }
        }
        return $this->_canReturnToStock;
    }

    public function canSendCreditmemoEmail()
    {
        return $this->_salesData->canSendNewCreditmemoEmail($this->getOrder()->getStore()->getId());
    }
}
