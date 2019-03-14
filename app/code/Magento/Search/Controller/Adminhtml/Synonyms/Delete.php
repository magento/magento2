<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Search\Controller\Adminhtml\Synonyms;

/**
 * Delete Controller
 */
class Delete extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Search::synonyms';

    /**
     * @var \Psr\Log\LoggerInterface $logger
     */
    private $logger;

    /**
     * @var \Magento\Search\Api\SynonymGroupRepositoryInterface $synGroupRepository
     */
    private $synGroupRepository;

    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Search\Api\SynonymGroupRepositoryInterface $synGroupRepository
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Search\Api\SynonymGroupRepositoryInterface $synGroupRepository,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->synGroupRepository = $synGroupRepository;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * Delete action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('group_id');
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id) {
            try {
                /** @var \Magento\Search\Model\SynonymGroup $synGroupModel */
                $synGroupModel = $this->synGroupRepository->get($id);
                $this->synGroupRepository->delete($synGroupModel);
                $this->messageManager->addSuccessMessage(__('The synonym group has been deleted.'));
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $this->logger->error($e);
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('An error was encountered while performing delete operation.')
                );
                $this->logger->error($e);
            }
        } else {
            $this->messageManager->addErrorMessage(__('We can\'t find a synonym group to delete.'));
        }

        return $resultRedirect->setPath('*/*/');
    }
}
