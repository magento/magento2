<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Controller\Adminhtml\Synonyms;

use Magento\Search\Model\Synonym\MergeConflictException;

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

            // Pre-process data and save it to model
            // Extract website_id and store_id out of scope_id
            // scope_id = website_id:store_id
            $tokens = explode(':', $data['scope_id']);
            $data['website_id'] = $tokens[0];
            $data['store_id'] = $tokens[1];

            // Remove unnecessary white spaces and convert synonyms to lower case
            $words = explode(',', $data['synonyms']);
            $words = array_map('trim', $words);
            $data['synonyms'] = strtolower(implode(',', $words));

            $synGroupModel->setData($data);

            // save the data
            /** @var \Magento\Search\Model\SynonymGroupRepository $synGroupRepository */
            $synGroupRepository = $this->_objectManager->create('Magento\Search\Model\SynonymGroupRepository');
            if (isset($data['mergeOnConflict']) && $data['mergeOnConflict'] === 'true') {
                $synGroupRepository->save($synGroupModel);
                $this->getMessageManager()->addSuccessMessage(__('You saved the synonym group.'));
            } else {
                try {
                    $synGroupRepository->save($synGroupModel, true);
                    $this->getMessageManager()->addSuccessMessage(__('You saved the synonym group.'));
                } catch (MergeConflictException $exception) {
                    $this->getMessageManager()->addErrorMessage($this->getErrorMessage($exception));
                    $this->_getSession()->setFormData($data);
                    return $resultRedirect->setPath('*/*/edit', ['group_id' => $synGroupModel->getId()]);
                }
            }

            // check if 'Save and Continue'
            if ($this->getRequest()->getParam('back')) {
                return $resultRedirect->setPath('*/*/edit', ['group_id' => $synGroupModel->getId()]);
            }
        }
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * Constructs the error message from the Merge conflict exception
     *
     * @param MergeConflictException $exception
     * @return \Magento\Framework\Phrase
     */
    private function getErrorMessage(MergeConflictException $exception)
    {
        $data = $this->getRequest()->getPostValue();
        $conflictingSynonyms = $exception->getConflictingSynonyms();

        foreach ($conflictingSynonyms as $key => $conflictingSynonym) {
            $conflictingSynonyms[$key] = '(' . implode(',', $conflictingSynonym) . ')';
        }

        if (count($conflictingSynonyms) == 1) {
            $conflictingSynonymsMessage = __(
                'The terms you entered, (%1), ' .
                'belong to 1 existing synonym group, %2. ' .
                'Select the "Merge existing synonyms" checkbox so the terms can be merged.',
                $data['synonyms'],
                $conflictingSynonyms[0]
            );
        } else {
            $lastConflict = array_pop($conflictingSynonyms);
            $conflictingInfo = implode(', ', $conflictingSynonyms);
            $conflictingSynonymsMessage = __(
                'The terms you entered, (%1), ' .
                'belong to %2 existing synonym groups, %3 and %4. ' .
                'Select the "Merge existing synonyms" checkbox so the terms can be merged.',
                $data['synonyms'],
                count($conflictingSynonyms) + 1,
                $conflictingInfo,
                $lastConflict
            );
        }

        return $conflictingSynonymsMessage;
    }
}
