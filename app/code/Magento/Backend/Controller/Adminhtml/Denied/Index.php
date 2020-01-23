<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Controller\Adminhtml\Denied;

use Magento\Backend\Controller\Adminhtml\Denied;
use Magento\Framework\App\Action\HttpGetActionInterface as HttpGet;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPost;

/**
 * Denied Action
 */
class Index extends Denied implements HttpGet, HttpPost
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
