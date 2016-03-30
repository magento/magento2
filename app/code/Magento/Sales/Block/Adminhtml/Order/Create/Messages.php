<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Adminhtml\Order\Create;

/**
 * Order create errors block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
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
