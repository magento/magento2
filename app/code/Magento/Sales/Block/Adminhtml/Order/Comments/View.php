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
namespace Magento\Sales\Block\Adminhtml\Order\Comments;

/**
 * Invoice view  comments form
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class View extends \Magento\Backend\Block\Template
{
    /**
     * Sales data
     *
     * @var \Magento\Sales\Helper\Data
     */
    protected $_salesData = null;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Sales\Helper\Data $salesData
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Sales\Helper\Data $salesData,
        array $data = array()
    ) {
        $this->_salesData = $salesData;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve required options from parent
     *
     * @return void
     * @throws \Magento\Framework\Model\Exception
     */
    protected function _beforeToHtml()
    {
        if (!$this->getParentBlock()) {
            throw new \Magento\Framework\Model\Exception(__('Please correct the parent block for this block.'));
        }
        $this->setEntity($this->getParentBlock()->getSource());
        parent::_beforeToHtml();
    }

    /**
     * Preparing global layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->addChild(
            'submit_button',
            'Magento\Backend\Block\Widget\Button',
            array('id' => 'submit_comment_button', 'label' => __('Submit Comment'), 'class' => 'save')
        );
        return parent::_prepareLayout();
    }

    /**
     * Get submit url
     *
     * @return string|true
     */
    public function getSubmitUrl()
    {
        return $this->getUrl('*/*/addComment', array('id' => $this->getEntity()->getId()));
    }

    /**
     * @return bool
     */
    public function canSendCommentEmail()
    {
        switch ($this->getParentType()) {
            case 'invoice':
                return $this->_salesData->canSendInvoiceCommentEmail(
                    $this->getEntity()->getOrder()->getStore()->getId()
                );
            case 'shipment':
                return $this->_salesData->canSendShipmentCommentEmail(
                    $this->getEntity()->getOrder()->getStore()->getId()
                );
            case 'creditmemo':
                return $this->_salesData->canSendCreditmemoCommentEmail(
                    $this->getEntity()->getOrder()->getStore()->getId()
                );
        }
        return true;
    }
}
