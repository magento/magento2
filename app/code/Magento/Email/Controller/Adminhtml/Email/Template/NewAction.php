<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Email\Controller\Adminhtml\Email\Template;

/**
 * Class \Magento\Email\Controller\Adminhtml\Email\Template\NewAction
 *
 * @since 2.0.0
 */
class NewAction extends \Magento\Email\Controller\Adminhtml\Email\Template
{
    /**
     * New transactional email action
     *
     * @return void
     * @since 2.0.0
     */
    public function execute()
    {
        $this->_forward('edit');
    }
}
