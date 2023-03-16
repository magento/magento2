<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Search\Controller\Adminhtml\Synonyms;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect as ResultRedirect;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Search\Api\SynonymGroupRepositoryInterface;
use Magento\Search\Model\ResourceModel\SynonymGroup\CollectionFactory;
use Magento\Ui\Component\MassAction\Filter;

/**
 * Mass-Delete Controller.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MassDelete extends Action implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session.
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Search::synonyms';

    /**
     * MassDelete constructor.
     *
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param SynonymGroupRepositoryInterface $synGroupRepository
     */
    public function __construct(
        Context $context,
        private readonly Filter $filter,
        private readonly CollectionFactory $collectionFactory,
        private readonly SynonymGroupRepositoryInterface $synGroupRepository
    ) {
        parent::__construct($context);
    }

    /**
     * Execute action.
     *
     * @return ResultRedirect
     * @throws LocalizedException|Exception
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $collectionSize = $collection->getSize();

        $deletedItems = 0;
        foreach ($collection as $synonymGroup) {
            try {
                $this->synGroupRepository->delete($synonymGroup);
                $deletedItems++;
            } catch (Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }
        if ($deletedItems != 0) {
            if ($collectionSize != $deletedItems) {
                $this->messageManager->addErrorMessage(
                    __('Failed to delete %1 synonym group(s).', $collectionSize - $deletedItems)
                );
            }

            $this->messageManager->addSuccessMessage(
                __('A total of %1 synonym group(s) have been deleted.', $deletedItems)
            );
        }
        /** @var ResultRedirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }
}
