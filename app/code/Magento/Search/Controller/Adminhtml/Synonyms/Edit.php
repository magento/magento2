<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Controller\Adminhtml\Synonyms;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context as ActionContextAlias;
use Magento\Backend\Model\View\Result\Redirect as ResultRedirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Registry;
use Magento\Search\Api\Data\SynonymGroupInterface;
use Magento\Search\Api\SynonymGroupRepositoryInterface;
use Magento\Search\Controller\RegistryConstants;

class Edit extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Search::synonyms';

    /**
     * Edit constructor.
     *
     * @param ActionContextAlias $context
     * @param Registry $registry
     * @param ResultPageBuilder $pageBuilder
     * @param SynonymGroupRepositoryInterface $synGroupRepository
     */
    public function __construct(
        ActionContextAlias $context,
        private readonly Registry $registry,
        private readonly ResultPageBuilder $pageBuilder,
        private readonly SynonymGroupRepositoryInterface $synGroupRepository
    ) {
        parent::__construct($context);
    }

    /**
     * Edit Synonym Group
     *
     * @return ResultInterface
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        // 1. Get ID and create model
        $groupId = $this->getRequest()->getParam('group_id');
        /** @var SynonymGroupInterface $synGroup */
        $synGroup = $this->synGroupRepository->get($groupId);

        // 2. Initial checking
        if ($groupId && (!$synGroup->getGroupId())) {
                $this->messageManager->addErrorMessage(__('This synonyms group no longer exists.'));
                /** @var ResultRedirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
        }

        // 3. Set entered data if was error when we do save
        $data = $this->_session->getFormData(true);
        if (!empty($data)) {
            $synGroup->setGroupId($data['group_id']);
            $synGroup->setStoreId($data['store_id']);
            $synGroup->setWebsiteId($data['website_id']);
            $synGroup->setSynonymGroup($data['synonyms']);
        }

        // 4. Register model to use later in save
        $this->registry->register(
            RegistryConstants::SEARCH_SYNONYMS,
            $synGroup
        );

        // 5. Build edit synonyms group form
        $resultPage = $this->pageBuilder->build();
        $resultPage->addBreadcrumb(
            $groupId ? __('Edit Synonym Group') : __('New Synonym Group'),
            $groupId ? __('Edit Synonym Group') : __('New Synonym Group')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Synonym Group'));
        $resultPage->getConfig()->getTitle()->prepend(
            $synGroup->getGroupId() ? $synGroup->getSynonymGroup() : __('New Synonym Group')
        );
        return $resultPage;
    }
}
