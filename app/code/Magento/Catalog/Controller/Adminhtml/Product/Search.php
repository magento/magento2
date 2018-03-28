<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Catalog\Controller\Adminhtml\Product;

use Magento\Catalog\Api\Data\ProductInterface;

/**
 * Controller to search product for ui-select component
 */
class Search extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var \Magento\Ui\DataProvider\AddFilterToCollectionInterface[]
     */
    private $filterStrategies;

    /**
     * Search constructor.
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Backend\App\Action\Context $context
     * @param array $filterStrategies
     */
    public function __construct(
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Backend\App\Action\Context $context,
        array $filterStrategies = []
    ) {
        $this->resultJsonFactory = $jsonFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->filterStrategies = $filterStrategies;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute() : \Magento\Framework\Controller\ResultInterface
    {
        $searchKey = $this->getRequest()->getParam('searchKey');
        $pageNum = $this->getRequest()->getParam('page');
        $limit = $this->getRequest()->getParam('limit');

        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection */
        $productCollection = $this->productCollectionFactory->create();
        $productCollection->addAttributeToSelect(ProductInterface::NAME);
        $productCollection->setPage($pageNum, $limit);
        $this->addFilter($productCollection, 'fulltext', $searchKey);
        $productById = [];
        /** @var  ProductInterface $product */
        foreach ($productCollection as $product) {
            $productId = $product->getId();
            $productById[$productId] = [
                'value' => $productId,
                'label' => $product->getName(),
                'is_active' => $product->getStatus(),
                'path' => $product->getSku(),
                'optgroup' => false
            ];
        }
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($productById);
    }

    /**
     * Add filter to collection based on search data
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @param string $filterType
     * @param string $searchKey
     * @return void
     */
    private function addFilter(
        \Magento\Catalog\Model\ResourceModel\Product\Collection $collection,
        string $filterType,
        string $searchKey) : void {
        if (isset($this->filterStrategies[$filterType])) {
            $this->filterStrategies[$filterType]
                ->addFilter(
                    $collection,
                    $filterType,
                    [$filterType => $searchKey]
                );
        } else {
            $collection->addAttributeToSelect(
                [ProductInterface::NAME, ProductInterface::SKU],
                ['like' => "%{$searchKey}%"]
            );
        }
    }

}
