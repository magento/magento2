<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Controller\Adminhtml\System\Design;

class Edit extends \Magento\Backend\Controller\Adminhtml\System\Design
{
    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Magento_Backend::system_design_schedule');
        $resultPage->getConfig()->getTitle()->prepend(__('Store Design'));
        $id = (int)$this->getRequest()->getParam('id');
        $design = $this->_objectManager->create('Magento\Framework\App\DesignInterface');

        if ($id) {
            $design->load($id);
        }

        $resultPage->getConfig()->getTitle()->prepend(
            $design->getId() ? __('Edit Store Design Change') : __('New Store Design Change')
        );

        $this->_coreRegistry->register('design', $design);

        $resultPage->addContent($resultPage->getLayout()->createBlock('Magento\Backend\Block\System\Design\Edit'));
        $resultPage->addLeft(
            $resultPage->getLayout()->createBlock('Magento\Backend\Block\System\Design\Edit\Tabs', 'design_tabs')
        );

        return $resultPage;
    }
}
