<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Order\PrintOrder;

use Magento\Framework\View\Element\AbstractBlock;

/**
 * Sales order details block
 *
 * @api
 * @since 2.0.0
 */
class Creditmemo extends \Magento\Sales\Block\Items\AbstractItems
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     * @since 2.0.0
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Payment\Helper\Data
     * @since 2.0.0
     */
    protected $_paymentHelper;

    /**
     * @var \Magento\Sales\Model\Order\Address\Renderer
     * @since 2.0.0
     */
    protected $addressRenderer;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Payment\Helper\Data $paymentHelper
     * @param \Magento\Sales\Model\Order\Address\Renderer $addressRenderer
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Payment\Helper\Data $paymentHelper,
        \Magento\Sales\Model\Order\Address\Renderer $addressRenderer,
        array $data = []
    ) {
        $this->addressRenderer = $addressRenderer;
        $this->_paymentHelper = $paymentHelper;
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * @return void
     * @since 2.0.0
     */
    protected function _prepareLayout()
    {
        $this->pageConfig->getTitle()->set(__('Order # %1', $this->getOrder()->getRealOrderId()));
        $infoBlock = $this->_paymentHelper->getInfoBlock($this->getOrder()->getPayment(), $this->getLayout());
        $this->setChild('payment_info', $infoBlock);
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getBackUrl()
    {
        return $this->getUrl('*/*/history');
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getPrintUrl()
    {
        return $this->getUrl('*/*/print');
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getPaymentInfoHtml()
    {
        return $this->getChildHtml('payment_info');
    }

    /**
     * @return array|null
     * @since 2.0.0
     */
    public function getOrder()
    {
        return $this->_coreRegistry->registry('current_order');
    }

    /**
     * @return array|null
     * @since 2.0.0
     */
    public function getCreditmemo()
    {
        return $this->_coreRegistry->registry('current_creditmemo');
    }

    /**
     * @param AbstractBlock $renderer
     * @return $this
     * @since 2.0.0
     */
    protected function _prepareItem(AbstractBlock $renderer)
    {
        $renderer->setPrintStatus(true);
        return parent::_prepareItem($renderer);
    }

    /**
     * Get Creditmemo totals block html gor specific creditmemo
     *
     * @param   \Magento\Sales\Model\Order\Creditmemo $creditmemo
     * @return  string
     * @since 2.0.0
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
     * Formats order address to html, pdf and etc. formats
     *
     * @param \Magento\Sales\Model\Order\Address $address
     * @param string $format
     * @return null|string
     * @since 2.0.0
     */
    public function formatAddress(\Magento\Sales\Model\Order\Address $address, $format)
    {
        return $this->addressRenderer->format($address, $format);
    }
}
