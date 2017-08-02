<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Controller\Adminhtml\System\Store;

/**
 * Class \Magento\Backend\Controller\Adminhtml\System\Store\EditWebsite
 *
 * @since 2.0.0
 */
class EditWebsite extends \Magento\Backend\Controller\Adminhtml\System\Store
{
    /**
     * @return \Magento\Backend\Model\View\Result\Forward
     * @since 2.0.0
     */
    public function execute()
    {
        $this->_coreRegistry->register('store_type', 'website');
        /** @var \Magento\Backend\Model\View\Result\Forward $resultForward */
        $resultForward = $this->resultForwardFactory->create();
        return $resultForward->forward('editStore');
    }
}
