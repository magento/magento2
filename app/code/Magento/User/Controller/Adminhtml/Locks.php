<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Locked administrators controller
 */
namespace Magento\User\Controller\Adminhtml;

abstract class Locks extends \Magento\Backend\App\Action
{
    /**
     * Check whether access is allowed for current admin session
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_User::locks');
    }
}
