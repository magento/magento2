<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Controller\Adminhtml;

use Magento\Backend\App\AbstractAction;

/**
 * Index backend controller
 * @since 2.0.0
 */
abstract class Index extends AbstractAction
{
    /**
     * Check if user has permissions to access this controller
     *
     * @return bool
     * @since 2.0.0
     */
    protected function _isAllowed()
    {
        return true;
    }
}
