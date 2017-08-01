<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml\Order\View;

/**
 * Adminhtml sales order view gift messages controller
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
abstract class Giftmessage extends \Magento\Backend\App\Action
{
    /**
     * Retrieve gift message save model
     *
     * @return \Magento\GiftMessage\Model\Save
     * @since 2.0.0
     */
    protected function _getGiftmessageSaveModel()
    {
        return $this->_objectManager->get(\Magento\GiftMessage\Model\Save::class);
    }
}
