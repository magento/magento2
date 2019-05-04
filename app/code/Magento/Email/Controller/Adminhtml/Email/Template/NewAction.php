<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Email\Controller\Adminhtml\Email\Template;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;

class NewAction extends \Magento\Email\Controller\Adminhtml\Email\Template implements HttpGetActionInterface
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
