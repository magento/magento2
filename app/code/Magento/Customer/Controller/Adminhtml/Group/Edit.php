<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Adminhtml\Group;

/**
 * Class \Magento\Customer\Controller\Adminhtml\Group\Edit
 *
 * @since 2.0.0
 */
class Edit extends \Magento\Customer\Controller\Adminhtml\Group
{
    /**
     * Edit customer group action. Forward to new action.
     *
     * @return \Magento\Backend\Model\View\Result\Forward
     * @since 2.0.0
     */
    public function execute()
    {
        return $this->resultForwardFactory->create()->forward('new');
    }
}
