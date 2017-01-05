<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Widget\Controller\Adminhtml\Widget\Instance;

class NewAction extends \Magento\Widget\Controller\Adminhtml\Widget\Instance
{
    /**
     * New widget instance action (forward to edit action)
     *
     * @return void
     */
    public function execute()
    {
        $this->_forward('edit');
    }
}
