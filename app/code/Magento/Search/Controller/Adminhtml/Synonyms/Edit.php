<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Controller\Adminhtml\Synonyms;

/**
 * Class \Magento\Search\Controller\Adminhtml\Synonyms\Edit
 *
 * @since 2.1.0
 */
class Edit extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Search::synonyms';

    /**
     * @var \Magento\Framework\Registry $registry
     * @since 2.1.0
     */
    private $registry;

    /**
     * @var \Magento\Search\Controller\Adminhtml\Synonyms\ResultPageBuilder $pageBuilder
     * @since 2.1.0
     */
    private $pageBuilder;

    /**
     * @var \Magento\Search\Api\SynonymGroupRepositoryInterface $synGroupRepository
     * @since 2.1.0
     */
    private $synGroupRepository;

    /**
     * Edit constructor.
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Backend\Model\Session $session
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Search\Controller\Adminhtml\Synonyms\ResultPageBuilder $pageBuilder
     * @param \Magento\Search\Api\SynonymGroupRepositoryInterface $synGroupRepository
     * @since 2.1.0
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Search\Controller\Adminhtml\Synonyms\ResultPageBuilder $pageBuilder,
        \Magento\Search\Api\SynonymGroupRepositoryInterface $synGroupRepository
    ) {
        $this->registry = $registry;
        $this->synGroupRepository = $synGroupRepository;
        $this->pageBuilder = $pageBuilder;
        parent::__construct($context);
    }

    /**
     * Edit Synonym Group
     *
     * @return \Magento\Framework\Controller\ResultInterface
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @since 2.1.0
     */
    public function execute()
    {
        // 1. Get ID and create model
        $groupId = $this->getRequest()->getParam('group_id');
        /** @var \Magento\Search\Api\Data\SynonymGroupInterface $synGroup */
        $synGroup = $this->synGroupRepository->get($groupId);

        // 2. Initial checking
        if ($groupId && (!$synGroup->getGroupId())) {
                $this->messageManager->addError(__('This synonyms group no longer exists.'));
                /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
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
            \Magento\Search\Controller\RegistryConstants::SEARCH_SYNONYMS,
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
