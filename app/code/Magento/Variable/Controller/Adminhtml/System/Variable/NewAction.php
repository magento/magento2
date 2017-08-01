<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Variable\Controller\Adminhtml\System\Variable;

/**
 * Create new variable form
 *
 * @api
 * @since 2.0.0
 */
class NewAction extends \Magento\Variable\Controller\Adminhtml\System\Variable
{
    /**
     * New Action (forward to edit action)
     *
     * @return \Magento\Backend\Model\View\Result\Forward
     * @since 2.0.0
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Forward $resultForward */
        $resultForward = $this->resultForwardFactory->create();
        return $resultForward->forward('edit');
    }
}
