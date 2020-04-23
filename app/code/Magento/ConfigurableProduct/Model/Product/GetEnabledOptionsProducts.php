<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Model\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Config;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\Query\JoinAttributeProcessor;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Product\CollectionFactory;
use Magento\Framework\Exception\LocalizedException;
use Zend_Db_Select_Exception;

/**
 * Retrieve configurable options service.
 */
class GetEnabledOptionsProducts
{
    /**
     * Product collection factory
     *
     * @var CollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var JoinAttributeProcessor
     */
    private $joinAttributeProcessor;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var Configurable
     */
    private $configurable;

    /**
     * @param CollectionFactory $productCollectionFactory
     * @param JoinAttributeProcessor $joinAttributeProcessor
     * @param Config $config
     * @param Type\Configurable $configurable
     */
    public function __construct(
        CollectionFactory $productCollectionFactory,
        JoinAttributeProcessor $joinAttributeProcessor,
        Config $config,
        Configurable $configurable
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->joinAttributeProcessor = $joinAttributeProcessor;
        $this->config = $config;
        $this->configurable = $configurable;
    }

    /**
     * Retrieve enabled configurable options.
     *
     * @param ProductInterface $product
     * @return ProductInterface[]
     * @throws LocalizedException
     * @throws Zend_Db_Select_Exception
     */
    public function execute(ProductInterface $product): array
    {
        $collection = $this->productCollectionFactory->create();
        $collection->setFlag('product_children', true);
        $collection->setProductFilter($product);
        if (null !== $this->configurable->getStoreFilter($product)) {
            $collection->addStoreFilter($this->configurable->getStoreFilter($product));
        }

        $collection->getSelect()->joinInner(
            ['cwd' => $collection->getConnection()->getTableName('catalog_product_index_website')],
            'product_website.website_id = cwd.website_id',
            []
        );

        $this->joinAttributeProcessor->process(
            $collection->getSelect(),
            ProductInterface::STATUS,
            Status::STATUS_ENABLED
        );
        $collection->addAttributeToSelect($this->getAttributesForCollection($product));
        $collection->addFilterByRequiredOptions();
        $collection->setStoreId($product->getStoreId());
        $collection->addMediaGalleryData();
        $collection->addTierPriceData();

        return $collection->getItems();
    }

    /**
     * @param ProductInterface $product
     * @return array
     */
    private function getAttributesForCollection(ProductInterface $product): array
    {
        $requiredAttributes = [
            'name',
            'price',
            'weight',
            'image',
            'thumbnail',
            'status',
            'visibility',
            'media_gallery',
        ];

        $usedAttributes = array_map(
            function ($attr) {
                return $attr->getAttributeCode();
            },
            $this->configurable->getUsedProductAttributes($product)
        );

        return array_unique(
            array_merge(
                $this->config->getProductAttributes(),
                $requiredAttributes,
                $usedAttributes
            )
        );
    }
}
