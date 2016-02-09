<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Search\Controller\Adminhtml\Synonyms;

/**
 * Mass-Delete Controller
 */
class MassDelete extends \Magento\Search\Controller\Adminhtml\Synonyms
{
    /**
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    protected $filter;

    /**
     * @var \Magento\Search\Model\ResourceModel\SynonymGroup\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * MassDelete constructor.
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $forwardFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Search\Model\EngineResolver $engineResolver
     * @param \Magento\Framework\Search\SearchEngine\ConfigInterface $searchFeatureConfig
     * @param \Magento\Ui\Component\MassAction\Filter $filter
     * @param \Magento\Search\Model\ResourceModel\SynonymGroup\CollectionFactory $collectionFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Backend\Model\View\Result\ForwardFactory $forwardFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Search\Model\EngineResolver $engineResolver,
        \Magento\Framework\Search\SearchEngine\ConfigInterface $searchFeatureConfig,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Magento\Search\Model\ResourceModel\SynonymGroup\CollectionFactory $collectionFactory
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        parent::__construct(
            $context,
            $resultPageFactory,
            $forwardFactory,
            $registry,
            $engineResolver,
            $searchFeatureConfig
        );
    }

    /**
     * Execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $collectionSize = $collection->getSize();

        $synonymGroupRepository = $this->_objectManager->create('Magento\Search\Api\SynonymGroupRepositoryInterface');

        $deletedItems = 0;
        foreach ($collection as $synonymGroup) {
            try {
                $synonymGroupRepository->delete($synonymGroup);
                $deletedItems++;
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }
        if ($deletedItems != 0) {
            if ($collectionSize != $deletedItems) {
                $this->messageManager->addError(
                    __('Failed to delete %1 synonym group(s).', $collectionSize - $deletedItems)
                );
            }

            $this->messageManager->addSuccess(
                __('A total of %1 synonym group(s) have been deleted.', $deletedItems)
            );
        }
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }
}
