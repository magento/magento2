<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Fixtures\FixturesAsserts;

/**
 * Class ProductAssert
 *
 * Class provides assertions about products count and products type
 * that helps validate them after running setup:performance:generate-fixtures command
 */
class ProductAssert
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\
     */
    protected $productCollectionFactory;

    /**
     * @var \Magento\Framework\DB\Sql\ColumnValueExpressionFactory
     */
    protected $expressionFactory;

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Framework\DB\Sql\ColumnValueExpressionFactory $expressionFactory
     */
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Framework\DB\Sql\ColumnValueExpressionFactory $expressionFactory
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->expressionFactory = $expressionFactory;
    }

    /**
     * Performs assertion on generated products count
     * Accepts products sku pattern to calculate count and theirs expected count
     *
     * @param string $skuPattern
     * @param int $expectedCount
     * @return void
     * @throws \AssertionError
     */
    public function assertProductsCount($skuPattern, $expectedCount)
    {
        $productSkuPattern = str_replace('%s', '[0-9]+', $skuPattern);
        $productCollection = $this->productCollectionFactory->create();
        $productCollection
            ->getSelect()
            ->where('sku ?', $this->expressionFactory->create([
                'expression' => 'REGEXP \'^' . $productSkuPattern . '$\''
            ]));

        if ($expectedCount !== count($productCollection)) {
            throw new \AssertionError(
                sprintf(
                    'Expected amount of products with sku pattern "%s" not equals actual amount',
                    $skuPattern
                )
            );
        }
    }

    /**
     * Performs assertion that product has expected product type
     *
     * @param string $expectedProductType
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @throws \AssertionError
     */
    public function assertProductType($expectedProductType, $product)
    {
        if ($expectedProductType !== $product->getTypeId()) {
            throw new \AssertionError('Product type is wrong');
        }
    }
}
