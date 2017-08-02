<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Adminhtml\Order\Comments;

/**
 * Invoice view  comments form
 *
 * @api
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class View extends \Magento\Backend\Block\Template
{
    /**
     * Sales data
     *
     * @var \Magento\Sales\Helper\Data
     * @since 2.0.0
     */
    protected $_salesData = null;

    /**
     * @var \Magento\Sales\Helper\Admin
     * @since 2.0.0
     */
    private $adminHelper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Sales\Helper\Data $salesData
     * @param \Magento\Sales\Helper\Admin $adminHelper
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Sales\Helper\Data $salesData,
        \Magento\Sales\Helper\Admin $adminHelper,
        array $data = []
    ) {
        $this->_salesData = $salesData;
        parent::__construct($context, $data);
        $this->adminHelper = $adminHelper;
    }

    /**
     * Retrieve required options from parent
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 2.0.0
     */
    protected function _beforeToHtml()
    {
        if (!$this->getParentBlock()) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Please correct the parent block for this block.')
            );
        }
        $this->setEntity($this->getParentBlock()->getSource());
        parent::_beforeToHtml();
    }

    /**
     * Preparing global layout
     *
     * @return $this
     * @since 2.0.0
     */
    protected function _prepareLayout()
    {
        $this->addChild(
            'submit_button',
            \Magento\Backend\Block\Widget\Button::class,
            ['id' => 'submit_comment_button', 'label' => __('Submit Comment'), 'class' => 'action-secondary save']
        );
        return parent::_prepareLayout();
    }

    /**
     * Get submit url
     *
     * @return string|true
     * @since 2.0.0
     */
    public function getSubmitUrl()
    {
        return $this->getUrl('*/*/addComment', ['id' => $this->getEntity()->getId()]);
    }

    /**
     * @return bool
     * @since 2.0.0
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

    /**
     * Replace links in string
     *
     * @param array|string $data
     * @param null|array $allowedTags
     * @return string
     * @since 2.0.0
     */
    public function escapeHtml($data, $allowedTags = null)
    {
        return $this->adminHelper->escapeHtmlWithLinks($data, $allowedTags);
    }
}
