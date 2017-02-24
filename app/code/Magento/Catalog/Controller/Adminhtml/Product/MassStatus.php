<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Controller\Adminhtml\Product;

use Magento\Backend\App\Action;
use Magento\Catalog\Controller\Adminhtml\Product;
use Magento\Framework\Controller\ResultFactory;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MassStatus extends \Magento\Catalog\Controller\Adminhtml\Product
{
    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Price\Processor
     */
    protected $_productPriceIndexerProcessor;

    /**
     * MassActions filter
     *
     * @var Filter
     */
    protected $filter;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @param Action\Context $context
     * @param Builder $productBuilder
     * @param \Magento\Catalog\Model\Indexer\Product\Price\Processor $productPriceIndexerProcessor
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        Product\Builder $productBuilder,
        \Magento\Catalog\Model\Indexer\Product\Price\Processor $productPriceIndexerProcessor,
        Filter $filter,
        CollectionFactory $collectionFactory
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->_productPriceIndexerProcessor = $productPriceIndexerProcessor;
        parent::__construct($context, $productBuilder);
    }

    /**
     * Validate batch of products before theirs status will be set
     *
     * @param array $productIds
     * @param int $status
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function _validateMassStatus(array $productIds, $status)
    {
        if ($status == \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED) {
            if (!$this->_objectManager->create(\Magento\Catalog\Model\Product::class)->isProductsHasSku($productIds)) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Please make sure to define SKU values for all processed products.')
                );
            }
        }
    }

    /**
     * Update product(s) status action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $productIds = $collection->getAllIds();
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        $status = (int) $this->getRequest()->getParam('status');
        $filters = (array)$this->getRequest()->getParam('filters', []);

        if (isset($filters['store_id'])) {
            $storeId = (int)$filters['store_id'];
        }

        try {
            $this->_validateMassStatus($productIds, $status);
            $this->_objectManager->get(\Magento\Catalog\Model\Product\Action::class)
                ->updateAttributes($productIds, ['status' => $status], $storeId);
            $this->messageManager->addSuccess(__('A total of %1 record(s) have been updated.', count($productIds)));
            $this->_productPriceIndexerProcessor->reindexList($productIds);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->_getSession()->addException($e, __('Something went wrong while updating the product(s) status.'));
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('catalog/*/', ['store' => $storeId]);
    }
}
