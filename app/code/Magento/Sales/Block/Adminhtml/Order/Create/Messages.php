<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Adminhtml\Order\Create;

/**
 * Order create errors block
 *
 * @api
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 100.0.2
 */
class Messages extends \Magento\Framework\View\Element\Messages
{
    /**
     * Preparing global layout
     *
     * @return void
     */
    protected function _prepareLayout()
    {
        $this->addMessages($this->messageManager->getMessages(true));
        parent::_prepareLayout();
    }
}
