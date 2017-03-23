<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Sales order view items block
 */
namespace Magento\Sales\Block\Order\Creditmemo;

class Items extends \Magento\Sales\Block\Items\AbstractItems
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve current order model instance
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->_coreRegistry->registry('current_order');
    }

    /**
     * @param object $creditmemo
     * @return string
     */
    public function getPrintCreditmemoUrl($creditmemo)
    {
        return $this->getUrl('*/*/printCreditmemo', ['creditmemo_id' => $creditmemo->getId()]);
    }

    /**
     * @param object $order
     * @return string
     */
    public function getPrintAllCreditmemosUrl($order)
    {
        return $this->getUrl('*/*/printCreditmemo', ['order_id' => $order->getId()]);
    }

    /**
     * Get creditmemo totals block html
     *
     * @param   \Magento\Sales\Model\Order\Creditmemo $creditmemo
     * @return  string
     */
    public function getTotalsHtml($creditmemo)
    {
        $totals = $this->getChildBlock('creditmemo_totals');
        $html = '';
        if ($totals) {
            $totals->setCreditmemo($creditmemo);
            $html = $totals->toHtml();
        }
        return $html;
    }

    /**
     * Get html of creditmemo comments block
     *
     * @param   \Magento\Sales\Model\Order\Creditmemo $creditmemo
     * @return  string
     */
    public function getCommentsHtml($creditmemo)
    {
        $html = '';
        $comments = $this->getChildBlock('creditmemo_comments');
        if ($comments) {
            $comments->setEntity($creditmemo)->setTitle(__('About Your Refund'));
            $html = $comments->toHtml();
        }
        return $html;
    }
}
