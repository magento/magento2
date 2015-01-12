<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Block\Product\ProductList;

use Magento\Catalog\Api\CategoryRepositoryInterface;

/**
 * Catalog product random items block
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class Random extends \Magento\Catalog\Block\Product\ListProduct
{
    /**
     * Product collection factory
     *
     * @var \Magento\Catalog\Model\Resource\Product\CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Core\Helper\PostData $postDataHelper
     * @param \Magento\Catalog\Model\Layer\Resolver $layerResolver
     * @param CategoryRepositoryInterface $categoryRepository
     * @param \Magento\Catalog\Model\Resource\Product\CollectionFactory $productCollectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Core\Helper\PostData $postDataHelper,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        CategoryRepositoryInterface $categoryRepository,
        \Magento\Catalog\Model\Resource\Product\CollectionFactory $productCollectionFactory,
        array $data = []
    ) {
        $this->_productCollectionFactory = $productCollectionFactory;
        parent::__construct(
            $context,
            $postDataHelper,
            $layerResolver,
            $categoryRepository,
            $data
        );
    }

    /**
     * @return \Magento\Catalog\Model\Resource\Product\Collection
     */
    protected function _getProductCollection()
    {
        if (is_null($this->_productCollection)) {
            /** @var \Magento\Catalog\Model\Resource\Product\Collection $collection */
            $collection = $this->_productCollectionFactory->create();
            $this->_catalogLayer->prepareProductCollection($collection);
            $collection->getSelect()->order('rand()');
            $collection->addStoreFilter();
            $numProducts = $this->getNumProducts() ? $this->getNumProducts() : 0;
            $collection->setPage(1, $numProducts);

            $this->_productCollection = $collection;
        }
        return $this->_productCollection;
    }
}
