<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Block\Product\ProductList;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

/**
 * Class \Magento\Catalog\Block\Product\ProductList\Promotion
 *
 * @since 2.0.0
 */
class Promotion extends \Magento\Catalog\Block\Product\ListProduct
{
    /**
     * Product collection factory
     *
     * @var CollectionFactory
     * @since 2.0.0
     */
    protected $_productCollectionFactory;

    /**
     * @var CategoryRepositoryInterface
     * @since 2.0.0
     */
    protected $categoryRepository;

    /**
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Framework\Data\Helper\PostHelper $postDataHelper
     * @param \Magento\Catalog\Model\Layer\Resolver $layerResolver
     * @param CategoryRepositoryInterface $categoryRepository
     * @param \Magento\Framework\Url\Helper\Data $urlHelper
     * @param CollectionFactory $productCollectionFactory
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Data\Helper\PostHelper $postDataHelper,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        CategoryRepositoryInterface $categoryRepository,
        \Magento\Framework\Url\Helper\Data $urlHelper,
        CollectionFactory $productCollectionFactory,
        array $data = []
    ) {
        $this->_productCollectionFactory = $productCollectionFactory;
        parent::__construct(
            $context,
            $postDataHelper,
            $layerResolver,
            $categoryRepository,
            $urlHelper,
            $data
        );
    }

    /**
     * @return Collection
     * @since 2.0.0
     */
    protected function _getProductCollection()
    {
        if ($this->_productCollection === null) {
            /** @var Collection $collection */
            $collection = $this->_productCollectionFactory->create();
            $this->_catalogLayer->prepareProductCollection($collection);

            $collection->addAttributeToFilter('promotion', 1)->addStoreFilter();

            $this->_productCollection = $collection;
        }
        return $this->_productCollection;
    }
}
