<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\User\Controller\Adminhtml\User;

/**
 * Class \Magento\User\Controller\Adminhtml\User\NewAction
 *
 */
class NewAction extends \Magento\User\Controller\Adminhtml\User
{
    /**
     * @return void
     */
    public function execute()
    {
        $this->_forward('edit');
    }
}
