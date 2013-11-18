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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Sales order details block
 */
namespace Magento\Sales\Block\Order\PrintOrder;

class Creditmemo extends \Magento\Sales\Block\Items\AbstractItems
{
    /**
     * Core registry
     *
     * @var \Magento\Core\Model\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Core\Block\Template\Context $context
     * @param \Magento\Core\Model\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Core\Helper\Data $coreData,
        \Magento\Core\Block\Template\Context $context,
        \Magento\Core\Model\Registry $registry,
        array $data = array()
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($coreData, $context, $data);
    }

    protected function _prepareLayout()
    {
        if ($headBlock = $this->getLayout()->getBlock('head')) {
            $headBlock->setTitle(__('Order # %1', $this->getOrder()->getRealOrderId()));
        }
        $this->setChild(
            'payment_info',
            $this->helper('Magento\Payment\Helper\Data')->getInfoBlock($this->getOrder()->getPayment())
        );
    }

    /**
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('*/*/history');
    }

    /**
     * @return string
     */
    public function getPrintUrl()
    {
        return $this->getUrl('*/*/print');
    }

    /**
     * @return string
     */
    public function getPaymentInfoHtml()
    {
        return $this->getChildHtml('payment_info');
    }

    public function getOrder()
    {
        return $this->_coreRegistry->registry('current_order');
    }

    public function getCreditmemo()
    {
        return $this->_coreRegistry->registry('current_creditmemo');
    }

    protected function _prepareItem(\Magento\Core\Block\AbstractBlock $renderer)
    {
        $renderer->setPrintStatus(true);
        return parent::_prepareItem($renderer);
    }

    /**
     * Get Creditmemo totals block html gor specific creditmemo
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
}
