<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Search\Controller\Adminhtml\Synonyms;

use Magento\Framework\App\Action\HttpPostActionInterface;

/**
 * Mass-Delete Controller.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MassDelete extends \Magento\Backend\App\Action implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session.
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Search::synonyms';

    /**
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    private $filter;

    /**
     * @var \Magento\Search\Model\ResourceModel\SynonymGroup\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var \Magento\Search\Api\SynonymGroupRepositoryInterface $synGroupRepository
     */
    private $synGroupRepository;

    /**
     * MassDelete constructor.
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Ui\Component\MassAction\Filter $filter
     * @param \Magento\Search\Model\ResourceModel\SynonymGroup\CollectionFactory $collectionFactory
     * @param \Magento\Search\Api\SynonymGroupRepositoryInterface $synGroupRepository
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Magento\Search\Model\ResourceModel\SynonymGroup\CollectionFactory $collectionFactory,
        \Magento\Search\Api\SynonymGroupRepositoryInterface $synGroupRepository
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->synGroupRepository = $synGroupRepository;
        parent::__construct($context);
    }

    /**
     * Execute action.
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
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
            } catch (\Exception $e) {
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
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }
}
