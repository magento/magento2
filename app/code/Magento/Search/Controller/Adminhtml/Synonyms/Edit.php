<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Controller\Adminhtml\Synonyms;

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
     */
    private $registry;

    /**
     * @var \Magento\Search\Helper\Actions $actionsHelper
     */
    private $actionsHelper;

    /**
     * @var \Magento\Search\Api\SynonymGroupRepositoryInterface $synGroupRepository
     */
    private $synGroupRepository;

    /**
     * Edit constructor.
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Backend\Model\Session $session
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Search\Helper\Actions $actionsHelper
     * @param \Magento\Search\Api\SynonymGroupRepositoryInterface $synGroupRepository
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Search\Helper\Actions $actionsHelper,
        \Magento\Search\Api\SynonymGroupRepositoryInterface $synGroupRepository
    ) {
        $this->registry = $registry;
        $this->synGroupRepository = $synGroupRepository;
        $this->actionsHelper = $actionsHelper;
        parent::__construct($context);
    }

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
        /** @var \Magento\Search\Model\SynonymGroup $synGroupModel */
        $synGroupModel = $this->synGroupRepository->get($groupId);

        // 2. Initial checking
        if ($groupId && (!$synGroupModel->getId())) {
                $this->messageManager->addError(__('This synonyms group no longer exists.'));
                /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
        }

        // 3. Set entered data if was error when we do save
        $data = $this->_session->getFormData(true);
        if (!empty($data)) {
            $synGroupModel->setData($data);
        }

        // 4. Register model to use later in save
        $this->registry->register(
            \Magento\Search\Controller\RegistryConstants::SEARCH_SYNONYMS,
            $synGroupModel
        );

        // 5. Build edit synonyms group form
        $resultPage = $this->actionsHelper->initAction();
        $resultPage->addBreadcrumb(
            $groupId ? __('Edit Synonym Group') : __('New Synonym Group'),
            $groupId ? __('Edit Synonym Group') : __('New Synonym Group')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Synonym Group'));
        $resultPage->getConfig()->getTitle()->prepend(
            $synGroupModel->getId() ? $synGroupModel->getSynonymGroup() : __('New Synonym Group')
        );
        return $resultPage;
    }
}
