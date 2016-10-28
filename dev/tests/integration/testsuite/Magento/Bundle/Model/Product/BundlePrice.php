<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Model\Product;

/**
 * Abstract class for testing bundle prices
 */
abstract class BundlePrice extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\TestFramework\Helper\Bootstrap */
    protected $objectManager;

    /** @var \Magento\Catalog\Api\ProductRepositoryInterface */
    protected $productRepository;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->productRepository = $this->objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
    }

    /**
     * Get test cases
     * @return array
     */
    abstract public function getTestCases();

    /**
     * @param array $strategyModifiers
     * @return \Magento\Catalog\Api\Data\ProductInterface
     */
    protected function prepareFixture($strategyModifiers)
    {
        $bundleProduct = $this->productRepository->get('spherical_horse_in_a_vacuum');

        foreach ($strategyModifiers as $modifier) {
            if (method_exists($this, $modifier['modifierName'])) {
                array_unshift($modifier['data'], $bundleProduct);
                $bundleProduct = call_user_func_array([$this, $modifier['modifierName']], $modifier['data']);
            }
        }

        $this->productRepository->save($bundleProduct);
        return $this->productRepository->get('spherical_horse_in_a_vacuum', false, null, true);
    }

    /**
     * Add simple product to bundle
     *
     * @param \Magento\Catalog\Model\Product $bundleProduct
     * @param array $optionsData
     * @return \Magento\Catalog\Model\Product
     */
    protected function addSimpleProduct(\Magento\Catalog\Model\Product $bundleProduct, array $optionsData)
    {
        $options = [];

        foreach ($optionsData as $optionData) {
            $links = [];
            $linksData = $optionData['links'];
            unset($optionData['links']);

            $option = $this->objectManager->create(\Magento\Bundle\Api\Data\OptionInterfaceFactory::class)
                ->create(['data' => $this->getFixtureForProductOption($optionData)])
                ->setSku($bundleProduct->getSku());

            foreach ($linksData as $linkData) {
                $linkData['option_id'] = $option->getId();
                $links[] = $this->objectManager->create(\Magento\Bundle\Api\Data\LinkInterfaceFactory::class)
                    ->create(['data' => $linkData]);
            }

            $option->setProductLinks($links);
            $options[] = $option;
        }

        $extension = $bundleProduct->getExtensionAttributes();
        $extension->setBundleProductOptions($options);
        $bundleProduct->setExtensionAttributes($extension);

        return $bundleProduct;
    }

    /**
     * @param array $fixture
     * @return array
     */
    private function getFixtureForProductCustomOption(array $fixture = [])
    {
        $fixture['title'] = 'Custom Option Title ' . microtime(true);
        $fixture['sku'] = 'custom_option_sku_' . microtime(true);

        return $fixture;
    }

    /**
     * @param \Magento\Catalog\Model\Product $bundleProduct
     * @param array $optionsData
     * @return \Magento\Catalog\Model\Product
     */
    protected function addCustomOption(\Magento\Catalog\Model\Product $bundleProduct, array $optionsData)
    {
        /** @var \Magento\Catalog\Api\Data\ProductCustomOptionInterfaceFactory $customOptionFactory */
        $customOptionFactory = $this->objectManager
            ->create(\Magento\Catalog\Api\Data\ProductCustomOptionInterfaceFactory::class);

        $options = [];
        foreach ($optionsData as $optionData) {
            $customOption = $customOptionFactory->create(
                [
                    'data' => $this->getFixtureForProductCustomOption($optionData)
                ]
            );
            $customOption->setProductSku($bundleProduct->getSku());
            $customOption->setOptionId(null);

            $options[] = $customOption;
        }

        $bundleProduct->setOptions($options);
        $bundleProduct->setCanSaveCustomOptions(true);

        return $bundleProduct;
    }

    /**
     * @param array $fixture
     * @return array
     */
    private function getFixtureForProductOption(array $fixture = [])
    {
        $fixture['title'] = 'Option title' . microtime(true);

        return $fixture;
    }
}
