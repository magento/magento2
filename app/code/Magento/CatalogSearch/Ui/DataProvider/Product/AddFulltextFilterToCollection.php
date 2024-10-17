<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Ui\DataProvider\Product;

use Magento\CatalogSearch\Model\ResourceModel\Fulltext\BackendCollection;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Product\CollectionFactory as ConfigurableCollectionFactory;
use Magento\Framework\Data\Collection;
use Magento\Ui\DataProvider\AddFilterToCollectionInterface;

/**
 * Class AddFulltextFilterToCollection
 */
class AddFulltextFilterToCollection implements AddFilterToCollectionInterface
{
    private BackendCollection $backendCollection;
    private ConfigurableCollectionFactory $linkCollectionFactory;

    /**
     * @param BackendCollection $backendCollection
     */
    public function __construct(
        BackendCollection $backendCollection,
        ConfigurableCollectionFactory $linkCollectionFactory
    ) {
        $this->backendCollection = $backendCollection;
        $this->linkCollectionFactory = $linkCollectionFactory;
    }

    /**
     * @inheritdoc
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function addFilter(Collection $collection, $field, $condition = null)
    {
        /** @var $collection \Magento\Catalog\Model\ResourceModel\Product\Collection */
        if (isset($condition['fulltext']) && (string)$condition['fulltext'] !== '') {
            $this->backendCollection->addSearchFilter((string) $condition['fulltext']);
            $productIds = $this->backendCollection->load()->getAllIds();
            $simpleProductIds = [];
            if (empty($productIds)) {
                //add dummy id to prevent returning full unfiltered collection
                $productIds = -1;
            } else {
                $simpleProductIds = $this->getSimpleProductIds($this->backendCollection->getItems());
            }
            $collection->addIdFilter(array_merge($simpleProductIds, $productIds));
        }
    }

    /**
     * @param array $products
     * @return array
     */
    private function getSimpleProductIds(array $products): array
    {
        /** @var \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Product\Collection $collection */
        $collection = $this->linkCollectionFactory->create();
        $collection->setProductListFilter($products);
        $productIds = $collection->load()->getAllIds();

        return $productIds;
    }
}
