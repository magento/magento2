<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Controller\Adminhtml\Synonyms;

use Magento\Search\Model\SynonymGroup;
use Magento\Search\Model\SynonymGroupRepository;

class Save extends \Magento\Search\Controller\Adminhtml\Synonyms
{
    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        // check if data sent
        $data = $this->getRequest()->getPostValue();

        if ($data) {
            $synGroupId = $this->getRequest()->getParam('group_id');

            if (empty($data['group_id'])) {
                $data['group_id'] = null;
            }

            // Create model and load any existing data
            /** @var \Magento\Search\Model\SynonymGroup $synGroupModel */
            $synGroupModel = $this->_objectManager->create('Magento\Search\Model\SynonymGroup')->load($synGroupId);

            if (!$synGroupModel->getId() && $synGroupId) {
                $this->messageManager->addError(__('This synonym group no longer exists.'));
                return $resultRedirect->setPath('*/*/');
            }

            // init model and set data
            $synGroupModel->setData($data);

            // save the data
            /** @var \Magento\Search\Model\SynonymGroupRepository $synGroupRepository */
            $synGroupRepository = $this->_objectManager->create('Magento\Search\Model\SynonymGroupRepository');
            $synGroupRepository->save($synGroupModel);


            // check if 'Save and Continue'
            if ($this->getRequest()->getParam('back')) {
                return $resultRedirect->setPath('*/*/edit', ['group_id' => $synGroupModel->getId()]);
            }
        }
        return $resultRedirect->setPath('*/*/');
    }
}
