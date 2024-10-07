<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Search\Controller\Adminhtml\Synonyms;

use Exception;
use Magento\Backend\App\Action\Context as ActionContext;
use Magento\Backend\Model\View\Result\Redirect as ResultRedirect;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Search\Api\SynonymGroupRepositoryInterface;
use Magento\Search\Model\SynonymGroup;
use Psr\Log\LoggerInterface;

/**
 * Delete Controller
 */
class Delete extends \Magento\Backend\App\Action implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Search::synonyms';

    /**
     * @var LoggerInterface $logger
     */
    private $logger;

    /**
     * @var SynonymGroupRepositoryInterface $synGroupRepository
     */
    private $synGroupRepository;

    /**
     * Constructor
     *
     * @param ActionContext $context
     * @param SynonymGroupRepositoryInterface $synGroupRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        ActionContext $context,
        SynonymGroupRepositoryInterface $synGroupRepository,
        LoggerInterface $logger
    ) {
        $this->synGroupRepository = $synGroupRepository;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * Delete action
     *
     * @return ResultRedirect
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('group_id');
        /** @var ResultRedirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id) try {
            /** @var SynonymGroup $synGroupModel */
            $synGroupModel = $this->synGroupRepository->get($id);
            $this->synGroupRepository->delete($synGroupModel);
            $this->messageManager->addSuccessMessage(__('The synonym group has been deleted.'));
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->logger->error($e);
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage(
                __('An error was encountered while performing delete operation.')
            );
            $this->logger->error($e);
        } else {
            $this->messageManager->addErrorMessage(__('We can\'t find a synonym group to delete.'));
        }

        return $resultRedirect->setPath('*/*/');
    }
}
