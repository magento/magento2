<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Widget\Controller\Adminhtml\Widget\Instance;

use Magento\Widget\Controller\Adminhtml\Widget\Instance;

class NewAction extends Instance
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
