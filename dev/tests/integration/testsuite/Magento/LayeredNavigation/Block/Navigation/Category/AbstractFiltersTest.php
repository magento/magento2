<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LayeredNavigation\Block\Navigation\Category;

use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Layer\Filter\AbstractFilter;
use Magento\Catalog\Model\Layer\Filter\Item;
use Magento\CatalogSearch\Model\Indexer\Fulltext\Processor;
use Magento\Framework\Search\Request\Builder;
use Magento\Framework\Search\Request\Config;
use Magento\LayeredNavigation\Block\Navigation\AbstractCategoryTest;
use Magento\Search\Model\Search;
use Magento\Store\Model\Store;

/**
 * Base class for custom filters in navigation block on category page.
 */
abstract class AbstractFiltersTest extends AbstractCategoryTest
{
    /**
     * @var ProductAttributeRepositoryInterface
     */
    protected $attributeRepository;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();
        $this->attributeRepository = $this->objectManager->create(ProductAttributeRepositoryInterface::class);
        $this->productRepository = $this->objectManager->create(ProductRepositoryInterface::class);
    }

    /**
     * @inheritdoc
     */
    protected function prepareNavigationBlock(string $categoryName, int $storeId = Store::DEFAULT_STORE_ID): void
    {
        $this->objectManager->removeSharedInstance(Config::class);
        $this->objectManager->removeSharedInstance(Builder::class);
        $this->objectManager->removeSharedInstance(Search::class);
        $this->objectManager->create(Processor::class)->reindexAll();
        parent::prepareNavigationBlock($categoryName, $storeId);
    }

    /**
     * Returns filter with specified attribute.
     *
     * @param array $filters
     * @param string $code
     * @return AbstractFilter|null
     */
    protected function getFilterByCode(array $filters, string $code): ?AbstractFilter
    {
        $filter = array_filter(
            $filters,
            function (AbstractFilter $filter) use ($code) {
                return $filter->getData('attribute_model')
                    && $filter->getData('attribute_model')->getAttributeCode() == $code;
            }
        );

        return array_shift($filter);
    }

    /**
     * Updates attribute and products data.
     *
     * @param string $attributeCode
     * @param int $filterable
     * @param array $products
     * @return void
     */
    protected function updateAttributeAndProducts(
        string $attributeCode,
        int $filterable,
        array $products
    ): void {
        $attribute = $this->attributeRepository->get($attributeCode);
        $attribute->setData('is_filterable', $filterable);
        $this->attributeRepository->save($attribute);

        foreach ($products as $productSku => $stringValue) {
            $product = $this->productRepository->get($productSku, false, Store::DEFAULT_STORE_ID, true);
            $product->addData(
                [$attribute->getAttributeCode() => $attribute->getSource()->getOptionId($stringValue)]
            );
            $this->productRepository->save($product);
        }
    }

    /**
     * Returns filter items as array.
     *
     * @param AbstractFilter $filter
     * @return array
     */
    protected function prepareFilterItems(AbstractFilter $filter): array
    {
        $items = [];
        /** @var Item $item */
        foreach ($filter->getItems() as $item) {
            $item = [
                'label' => $item->getData('label'),
                'count' => $item->getData('count'),
            ];
            $items[] = $item;
        }

        return $items;
    }
}
