<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Controller\Adminhtml\Synonyms;

use Magento\Search\Model\Synonym\MergeConflictException;

/**
 * Class \Magento\Search\Controller\Adminhtml\Synonyms\Save
 *
 */
class Save extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Search::synonyms';

    /**
     * @var \Magento\Search\Api\SynonymGroupRepositoryInterface $synGroupRepository
     */
    private $synGroupRepository;

    /**
     * MassDelete constructor.
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Search\Api\SynonymGroupRepositoryInterface $synGroupRepository
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Search\Api\SynonymGroupRepositoryInterface $synGroupRepository
    ) {
        $this->synGroupRepository = $synGroupRepository;
        parent::__construct($context);
    }

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
            $synGroup = $this->synGroupRepository->get($synGroupId);

            if (!$synGroup->getGroupId() && $synGroupId) {
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

            $synGroup->setGroupId($data['group_id']);
            $synGroup->setStoreId($data['store_id']);
            $synGroup->setWebsiteId($data['website_id']);
            $synGroup->setSynonymGroup($data['synonyms']);

            // save the data
            if (isset($data['mergeOnConflict']) && $data['mergeOnConflict'] === 'true') {
                $this->synGroupRepository->save($synGroup);
                $this->getMessageManager()->addSuccessMessage(__('You saved the synonym group.'));
            } else {
                try {
                    $this->synGroupRepository->save($synGroup, true);
                    $this->getMessageManager()->addSuccessMessage(__('You saved the synonym group.'));
                } catch (MergeConflictException $exception) {
                    $this->getMessageManager()->addErrorMessage($this->getErrorMessage($exception));
                    $this->_getSession()->setFormData($data);
                    return $resultRedirect->setPath('*/*/edit', ['group_id' => $synGroup->getGroupId()]);
                }
            }

            // check if 'Save and Continue'
            if ($this->getRequest()->getParam('back')) {
                return $resultRedirect->setPath('*/*/edit', ['group_id' => $synGroup->getGroupId()]);
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
