<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\User\Controller\Adminhtml\User\Role;

use Magento\Framework\Controller\ResultFactory;

class Editrolegrid extends \Magento\User\Controller\Adminhtml\User\Role
{
    /**
     * Action for ajax request from assigned users grid
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        return $this->resultFactory->create(ResultFactory::TYPE_PAGE);
    }
}
