<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Controller\Adminhtml;

use Magento\Backend\App\AbstractAction;

/**
 * Auth backend controller
 */
abstract class Auth extends AbstractAction
{
    /**
     * Check if user has permissions to access this controller
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return true;
    }
}
