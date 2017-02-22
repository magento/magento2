<?php
/**
 * Adminhtml AdminNotification controller
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdminNotification\Controller\Adminhtml;

abstract class Notification extends \Magento\Backend\App\AbstractAction
{
    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_AdminNotification::show_list');
    }
}
