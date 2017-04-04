<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Fixtures\FixturesAsserts;

use Magento\Setup\Fixtures\SimpleProductsFixture;

/**
 * Class SimpleProductsAssert
 *
 * Class performs assertion that generated simple products are valid
 * after running setup:performance:generate-fixtures command
 */
class SimpleProductsAssert
{
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var \Magento\Setup\Fixtures\FixturesAsserts\ProductAssert
     */
    private $productAssert;

    /**
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\ConfigurableProduct\Api\OptionRepositoryInterface $optionRepository
     * @param \Magento\Setup\Fixtures\FixturesAsserts\ProductAssert $productAssert
     */
    public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\ConfigurableProduct\Api\OptionRepositoryInterface $optionRepository,
        \Magento\Setup\Fixtures\FixturesAsserts\ProductAssert $productAssert
    ) {
        $this->productRepository = $productRepository;
        $this->optionRepository = $optionRepository;
        $this->productAssert = $productAssert;
    }

    /**
     * Asserts that generated simple products are valid
     *
     * @return void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \AssertionError
     */
    public function assert()
    {
        $product = $this->productRepository->get(sprintf(SimpleProductsFixture::SKU_PATTERN, 1));
        $this->productAssert->assertProductsCount(SimpleProductsFixture::SKU_PATTERN, 2);
        $this->productAssert->assertProductType('simple', $product);
    }
}
