<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Widget\Controller\Adminhtml\Widget\Instance;

/**
 * Class \Magento\Widget\Controller\Adminhtml\Widget\Instance\NewAction
 *
 * @since 2.0.0
 */
class NewAction extends \Magento\Widget\Controller\Adminhtml\Widget\Instance
{
    /**
     * New widget instance action (forward to edit action)
     *
     * @return void
     * @since 2.0.0
     */
    public function execute()
    {
        $this->_forward('edit');
    }
}
