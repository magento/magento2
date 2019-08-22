<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Fixtures\FixturesAsserts;

use Magento\Setup\Fixtures\BundleProductsFixture;

/**
 * Class BundleProductsAssert
 *
 * Class performs assertion that generated bundle products are valid
 * after running setup:performance:generate-fixtures command
 */
class BundleProductsAssert
{
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var \Magento\Bundle\Model\Product\OptionList
     */
    private $optionList;

    /**
     * @var \Magento\Setup\Fixtures\FixturesAsserts\ProductAssert
     */
    private $productAssert;

    /**
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Bundle\Model\Product\OptionList $optionList
     * @param \Magento\Setup\Fixtures\FixturesAsserts\ProductAssert $productAssert
     */
    public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Bundle\Model\Product\OptionList $optionList,
        \Magento\Setup\Fixtures\FixturesAsserts\ProductAssert $productAssert
    ) {
        $this->productRepository = $productRepository;
        $this->optionList = $optionList;
        $this->productAssert = $productAssert;
    }

    /**
     * Asserts that generated bundled products are valid
     *
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \AssertionError
     */
    public function assert()
    {
        $bundleSkuSuffix = '2-2';
        $product = $this->productRepository->get(
            sprintf(BundleProductsFixture::SKU_PATTERN, 1, $bundleSkuSuffix)
        );

        $this->productAssert->assertProductsCount(
            sprintf(BundleProductsFixture::SKU_PATTERN, '%s', $bundleSkuSuffix),
            2
        );
        $this->productAssert->assertProductType('bundle', $product);

        if (2 !== count($this->optionList->getItems($product))) {
            throw new \AssertionError('Bundle options amount is wrong');
        }

        foreach ($this->optionList->getItems($product) as $option) {
            if (2 !== count($option->getProductLinks())) {
                throw new \AssertionError('Bundle option product links amount is wrong');
            }
        }

        return true;
    }
}
