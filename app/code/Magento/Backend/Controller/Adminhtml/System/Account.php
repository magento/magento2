<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Controller\Adminhtml\System;

use Magento\Backend\App\Action;

/**
 * Adminhtml account controller
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Account extends Action
{
    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Adminhtml::myaccount');
    }
}
