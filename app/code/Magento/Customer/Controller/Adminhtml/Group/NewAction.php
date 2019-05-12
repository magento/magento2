<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Adminhtml\Group;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Customer\Controller\RegistryConstants;

/**
 * Class NewAction
 *
 * @package Magento\Customer\Controller\Adminhtml\Group
 */
class NewAction extends \Magento\Customer\Controller\Adminhtml\Group implements HttpGetActionInterface
{
    /**
     * New customer group.
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Forward|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Forward $resultForward */
        $resultForward = $this->resultForwardFactory->create();
        return $resultForward->forward('edit');
    }

    /**
     * Initialize current group and set it in the registry.
     *
     * @deprecated 102.0.0
     * @return int
     */
    protected function __initGroup()
    {
        $groupId = $this->getRequest()->getParam('id');
        $this->_coreRegistry->register(RegistryConstants::CURRENT_GROUP_ID, $groupId);
        return $groupId;
    }
}
