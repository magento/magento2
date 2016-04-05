<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Controller\Adminhtml\Synonyms;

class Edit extends \Magento\Search\Controller\Adminhtml\Synonyms
{
    /**
     * Edit Synonym Group
     *
     * @return \Magento\Framework\Controller\ResultInterface
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        // 1. Get ID and create model
        $groupId = $this->getRequest()->getParam('group_id');
        $this->synonymGroupModel = $this->_objectManager->create('Magento\Search\Model\SynonymGroup');

        // 2. Initial checking
        if ($groupId) {
            $this->synonymGroupModel->load($groupId);
            if (!$this->synonymGroupModel->getId()) {
                $this->messageManager->addError(__('This synonyms group no longer exists.'));
                /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        }
        // 3. Set entered data if was error when we do save
        $data = $this->_objectManager->get('Magento\Backend\Model\Session')->getFormData(true);
        if (!empty($data)) {
            $this->synonymGroupModel->setData($data);
        }

        // 4. Register model to use later in save
        $this->registry->register(\Magento\Search\Controller\RegistryConstants::SEARCH_SYNONYMS, $this->synonymGroupModel);

        // 5. Build edit synonyms group form
        $resultPage = $this->_initAction();
        $resultPage->addBreadcrumb(
            $groupId ? __('Edit Synonym Group') : __('New Synonym Group'),
            $groupId ? __('Edit Synonym Group') : __('New Synonym Group')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Synonym Group'));
        $resultPage->getConfig()->getTitle()
            ->prepend($this->synonymGroupModel->getId() ? $this->synonymGroupModel->getSynonymGroup() : __('New Synonym Group'));
        return $resultPage;
    }
}
