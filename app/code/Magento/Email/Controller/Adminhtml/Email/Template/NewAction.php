<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Email\Controller\Adminhtml\Email\Template;

class NewAction extends \Magento\Email\Controller\Adminhtml\Email\Template
{
    /**
     * New transactional email action
     *
     * @return void
     */
    public function execute()
    {
        $this->_forward('edit');
    }
}
