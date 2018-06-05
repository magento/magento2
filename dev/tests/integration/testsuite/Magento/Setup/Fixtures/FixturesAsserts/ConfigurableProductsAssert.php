<?php
/**
 * Copyright © 2013-2018 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Fixtures\FixturesAsserts;

/**
 * Class ConfigurableProductsAssert
 *
 * Class performs assertion that generated configurable products are valid
 * after running setup:performance:generate-fixtures command
 */
class ConfigurableProductsAssert
{
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var \Magento\ConfigurableProduct\Api\OptionRepositoryInterface
     */
    private $optionRepository;

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
     * Asserts that generated configurable products are valid
     *
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \AssertionError
     */
    public function assert()
    {
        $productsMap = [
            'Configurable Product - Default %s' => [
                'attributes' => 1,
                'options' => 3,
                'amount' => 2,
            ],
            'Configurable Product - Color-Size %s' => [
                'attributes' => 2,
                'options' => 3,
                'amount' => 2,
            ],
            'Configurable Product 2-2 %s' => [
                'attributes' => 2,
                'options' => 2,
                'amount' => 2,
            ],
        ];

        foreach ($productsMap as $skuPattern => $expectedData) {
            $configurableSku = sprintf($skuPattern, 1);
            $product = $this->productRepository->get($configurableSku);
            $this->productAssert->assertProductsCount($skuPattern, $expectedData['amount']);
            $this->productAssert->assertProductType('configurable', $product);
            $options = $this->optionRepository->getList($configurableSku);

            if ($expectedData['attributes'] !== count($options)) {
                throw new \AssertionError('Configurable options amount is wrong');
            }

            if ($expectedData['options'] !== count($options[0]->getValues())) {
                throw new \AssertionError('Configurable option values amount is wrong');
            }
        }

        return true;
    }
}
