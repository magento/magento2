<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GiftMessage\Block\Adminhtml\Sales\Order\View;

/**
 * Adminhtml sales order view gift message form
 *
 * @api
 * @author     Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Form extends \Magento\Backend\Block\Template
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     * @since 2.0.0
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Indicates that block can display gift message form
     *
     * @return bool
     * @since 2.0.0
     */
    public function canDisplayGiftmessageForm()
    {
        $order = $this->_coreRegistry->registry('current_order');
        if ($order) {
            foreach ($order->getAllItems() as $item) {
                if ($item->getGiftMessageId()) {
                    return true;
                }
            }
        }
        return false;
    }
}
