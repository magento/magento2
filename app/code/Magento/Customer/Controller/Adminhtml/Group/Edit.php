<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Adminhtml\Group;

class Edit extends \Magento\Customer\Controller\Adminhtml\Group
{
    /**
     * Edit customer group action. Forward to new action.
     *
     * @return void
     */
    public function execute()
    {
        $this->_forward('new');
    }
}
