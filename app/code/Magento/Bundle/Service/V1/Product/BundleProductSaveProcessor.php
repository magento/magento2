<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Bundle\Service\V1\Product;

use Magento\Bundle\Service\V1\Data\Product\Option;
use Magento\Bundle\Service\V1\Product\Option\ReadService as OptionReadService;
use Magento\Bundle\Service\V1\Product\Option\WriteService as OptionWriteService;
use Magento\Framework\Service\Data\Eav\AttributeValue;
use Magento\Catalog\Model\Product as ProductModel;
use Magento\Catalog\Model\ProductRepository;
use Magento\Catalog\Model\Product\Type as ProductType;
use Magento\Catalog\Service\V1\Data\Product;
use Magento\Catalog\Service\V1\Product\ProductSaveProcessorInterface;

/**
 * Class to save bundle products
 */
class BundleProductSaveProcessor implements ProductSaveProcessorInterface
{
    /**
     * @var OptionWriteService
     */
    private $optionWriteService;

    /**
     * @var OptionReadService
     */
    private $optionReadService;

    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * Initialize dependencies.
     *
     * @param OptionWriteService $optionWriteService
     * @param OptionReadService $optionReadService
     * @param ProductRepository $productRepository
     */
    public function __construct(
        OptionWriteService $optionWriteService,
        OptionReadService $optionReadService,
        ProductRepository $productRepository
    ) {
        $this->optionWriteService = $optionWriteService;
        $this->optionReadService = $optionReadService;
        $this->productRepository = $productRepository;
    }

    /**
     * Process bundle-related attributes of product during its creation.
     *
     * @param ProductModel $product
     * @param Product $productData
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @return string
     */
    public function create(ProductModel $product, Product $productData)
    {
        return $product->getSku();
    }

    /**
     * Process bundle-related attributes of product after its creation.
     *
     * @param ProductModel $product
     * @param Product $productData
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @return string
     */
    public function afterCreate(ProductModel $product, Product $productData)
    {
        /** @var string $productSku */
        $productSku = $product->getSku();

        if ($product->getTypeId() != ProductType::TYPE_BUNDLE) {
            return $productSku;
        }

        /** @var Option[] $bundleProductOptions */

        /* @var AttributeValue $bundleProductOptionsAttrValue */
        $bundleProductOptionsAttrValue = $productData->getCustomAttribute('bundle_product_options');
        if (is_null($bundleProductOptionsAttrValue) || !is_array($bundleProductOptionsAttrValue->getValue())) {
            $bundleProductOptions = array();
        } else {
            $bundleProductOptions = $bundleProductOptionsAttrValue->getValue();
        }

        if (is_array($bundleProductOptions)) {
            foreach ($bundleProductOptions as $option) {
                $this->optionWriteService->add($productSku, $option);
            }
        }

        return $productSku;
    }

    /**
     * Update bundle-related attributes of product.
     *
     * @param string $sku
     * @param Product $updatedProduct
     * @return string
     */
    public function update($sku, Product $updatedProduct)
    {
        /**
         * @var Product $existingProduct
         */
        $existingProduct = $this->productRepository->get($sku, true);

        if ($existingProduct->getTypeId() != ProductType::TYPE_BUNDLE) {
            return $sku;
        }

        /**
         * @var Option[] $existingProductOptions
         */
        $existingProductOptions = $this->optionReadService->getList($sku);
        /**
         * @var Option[] $newProductOptions
         */
        /**
         * @var AttributeValue $newProductOptionsAttrValue
         */
        $newProductOptionsAttrValue = $updatedProduct->getCustomAttribute('bundle_product_options');
        if (is_null($newProductOptionsAttrValue) || !is_array($newProductOptionsAttrValue->getValue())) {
            $newProductOptions = array();
        } else {
            $newProductOptions = $newProductOptionsAttrValue->getValue();
        }
        /**
         * @var Option[] $optionsToDelete
         */
        $optionsToDelete = array_udiff($existingProductOptions, $newProductOptions, array($this, 'compareOptions'));
        foreach ($optionsToDelete as $option) {
            $this->optionWriteService->remove($sku, $option->getId());
        }
        /** @var Option[] $optionsToUpdate */
        $optionsToUpdate = array_uintersect(
            $existingProductOptions,
            $newProductOptions,
            array($this, 'compareOptions')
        );
        foreach ($optionsToUpdate as $option) {
            $this->optionWriteService->update($sku, $option->getId(), $option);
        }
        /**
         * @var Option[] $optionsToAdd
         */
        $optionsToAdd = array_udiff($newProductOptions, $existingProductOptions, array($this, 'compareOptions'));
        foreach ($optionsToAdd as $option) {
            $this->optionWriteService->add($sku, $option);
        }

        return $sku;
    }

    /**
     * Delete bundle-related attributes of product.
     *
     * @param Product $product
     * @return void
     */
    public function delete(Product $product)
    {
        if ($product->getTypeId() != ProductType::TYPE_BUNDLE) {
            return;
        }

        /**
         * @var string $productSku
         */
        $productSku = $product->getSku();

        /**
         * @var Option[] $bundleProductOptions
         */
        /**
         * @var AttributeValue $bundleProductOptionsAttrValue
         */
        $bundleProductOptionsAttrValue = $product->getCustomAttribute('bundle_product_options');
        if (is_null($bundleProductOptionsAttrValue) || !is_array($bundleProductOptionsAttrValue->getValue())) {
            $bundleProductOptions = array();
        } else {
            $bundleProductOptions = $bundleProductOptionsAttrValue->getValue();
        }
        foreach ($bundleProductOptions as $option) {
            $this->optionWriteService->remove($productSku, $option->getId());
        }
    }

    /**
     * Compare two options and determine if they are equal
     *
     * @param Option $firstOption
     * @param Option $secondOption
     * @return int
     */
    private function compareOptions(Option $firstOption, Option $secondOption)
    {
        if ($firstOption->getId() == $secondOption->getId()) {
            return 0;
        } else {
            return 1;
        }
    }
}
