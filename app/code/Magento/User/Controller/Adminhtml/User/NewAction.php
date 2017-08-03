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
 * @since 2.0.0
 */
class NewAction extends \Magento\User\Controller\Adminhtml\User
{
    /**
     * @return void
     * @since 2.0.0
     */
    public function execute()
    {
        $this->_forward('edit');
    }
}
