<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogRuleConfigurable\Plugin\CatalogRule\Model\Rule;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\CatalogRule\Model\Rule;
use Magento\Framework\DataObject;
use Magento\Catalog\Model\Product;

/**
 * Class Validation. Call validate method for configurable product instead simple product
 */
class Validation
{
    /**
     * @var Configurable
     */
    private $configurable;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @param Configurable $configurableType
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(Configurable $configurableType, ProductRepositoryInterface $productRepository)
    {
        $this->configurable = $configurableType;
        $this->productRepository = $productRepository;
    }

    /**
     * Define if it is needed to apply rule if parent configurable product match conditions
     *
     * @param Rule $rule
     * @param bool $validateResult
     * @param DataObject|Product $product
     * @return bool
     */
    public function afterValidate(Rule $rule, $validateResult, DataObject $product)
    {
        if (!$validateResult && ($configurableProducts = $this->configurable->getParentIdsByChild($product->getId()))) {
            foreach ($configurableProducts as $configurableProductId) {
                try {
                    $configurableProduct = $this->productRepository->getById(
                        $configurableProductId,
                        false,
                        $product->getStoreId()
                    );
                    $validateResult = $rule->getConditions()->validate($configurableProduct);
                    //If any of configurable product is valid for current rule, then their sub-product must be valid too
                    if ($validateResult) {
                        break;
                    }
                } catch (\Exception $e) {
                    continue;
                }
            }
        }
        return $validateResult;
    }
}
