<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
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
