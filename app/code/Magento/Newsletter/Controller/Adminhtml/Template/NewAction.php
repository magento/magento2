<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Newsletter\Controller\Adminhtml\Template;

/**
 * Class \Magento\Newsletter\Controller\Adminhtml\Template\NewAction
 *
 * @since 2.0.0
 */
class NewAction extends \Magento\Newsletter\Controller\Adminhtml\Template
{
    /**
     * Create new Newsletter Template
     *
     * @return void
     * @since 2.0.0
     */
    public function execute()
    {
        $this->_forward('edit');
    }
}
