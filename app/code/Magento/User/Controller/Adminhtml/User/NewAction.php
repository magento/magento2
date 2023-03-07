<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\User\Controller\Adminhtml\User;

use Magento\User\Controller\Adminhtml\User;

class NewAction extends User
{
    /**
     * @return void
     */
    public function execute()
    {
        $this->_forward('edit');
    }
}
