<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Adminhtml\Index;

class NewAction extends \Magento\Customer\Controller\Adminhtml\Index
{
    /**
     * Create new customer action
     *
     * @return void
     */
    public function execute()
    {
        $this->_forward('edit');
    }
}
