<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Controller\Adminhtml;

use Magento\Backend\App\AbstractAction;

/**
 * Index backend controller
 */
class Index extends AbstractAction
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
