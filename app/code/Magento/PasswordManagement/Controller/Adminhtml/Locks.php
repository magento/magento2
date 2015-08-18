<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Locked administrators controller
 *
 */
namespace Magento\PasswordManagement\Controller\Adminhtml;

abstract class Locks extends \Magento\Backend\App\Action
{
    /**
     * Check whether access is allowed for current admin session
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_PasswordManagement::locks');
    }
}
