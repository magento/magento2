<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Newsletter\Controller\Adminhtml\Template;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;

class NewAction extends \Magento\Newsletter\Controller\Adminhtml\Template implements HttpGetActionInterface
{
    /**
     * Create new Newsletter Template
     *
     * @return void
     */
    public function execute()
    {
        $this->_forward('edit');
    }
}
