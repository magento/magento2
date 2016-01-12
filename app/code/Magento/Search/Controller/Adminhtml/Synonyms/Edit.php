<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Controller\Adminhtml\Synonyms;

class Edit extends \Magento\Search\Controller\Adminhtml\Synonyms
{
    /**
     * Edit Synonyms Group
     *
     * @return \Magento\Framework\Controller\ResultInterface
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        // 1. Get ID and create model
        $groupId = $this->getRequest()->getParam('group_id');
        $synGroupModel = $this->_objectManager->create('Magento\Search\Model\SynonymGroup');

        // 2. Initial checking
        if ($groupId) {
            $synGroupModel->load($groupId);
            if (!$synGroupModel->getId()) {
                $this->messageManager->addError(__('This synonyms group no longer exists.'));
                /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        }
        // 3. Set entered data if was error when we do save
        $data = $this->_objectManager->get('Magento\Backend\Model\Session')->getFormData(true);
        if (!empty($data)) {
            $synGroupModel->setData($data);
        }

        // 4. Register model to use later in save
        $this->coreRegistry->register('search_synonyms', $synGroupModel);

        // 5. Build edit synonyms group form
        $resultPage = $this->_initAction();
        $resultPage->addBreadcrumb(
            $groupId ? __('Edit Synonyms Group') : __('New Synonyms Group'),
            $groupId ? __('Edit Synonyms Group') : __('New Synonyms Group')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Synonyms Group'));
        $resultPage->getConfig()->getTitle()
            ->prepend($synGroupModel->getId() ? $synGroupModel->getTitle() : __('New Synonyms Group'));
        return $resultPage;
    }
}
