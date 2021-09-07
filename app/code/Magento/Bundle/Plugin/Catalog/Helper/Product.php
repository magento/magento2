<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Plugin\Catalog\Helper;

use Magento\Catalog\Model\Product as ProductModel;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Helper\Product as Subject;
use Magento\Bundle\Model\Product\SelectionProductsDisabledRequired;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\CatalogInventory\Model\Configuration;
use Magento\Store\Model\ScopeInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;

/**
 * Plugin to not show bundle product when all products in required option are disabled
 */
class Product
{
    /**
     * @var SelectionProductsDisabledRequired
     */
    private $selectionProductsDisabledRequired;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @param SelectionProductsDisabledRequired $selectionProductsDisabledRequired
     * @param ScopeConfigInterface $scopeConfig
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        SelectionProductsDisabledRequired $selectionProductsDisabledRequired,
        ScopeConfigInterface $scopeConfig,
        ProductRepositoryInterface $productRepository
    ) {
        $this->selectionProductsDisabledRequired = $selectionProductsDisabledRequired;
        $this->scopeConfig = $scopeConfig;
        $this->productRepository = $productRepository;
    }

    /**
     * Do not show bundle product when all products in required option are disabled
     *
     * @param Subject $subject
     * @param bool $result
     * @param ProductModel|int $product
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterCanShow(Subject $subject, $result, $product)
    {
        if (is_int($product)) {
            $product = $this->productRepository->getById($product);
        }
        $productId = (int)$product->getEntityId();
        if ($result == false || $product->getTypeId() !== Type::TYPE_BUNDLE) {
            return $result;
        }
        $isShowOutOfStock = $this->scopeConfig->getValue(
            Configuration::XML_PATH_SHOW_OUT_OF_STOCK,
            ScopeInterface::SCOPE_STORE
        );
        if ($isShowOutOfStock) {
            return $result;
        }
        $productIdsDisabledRequired = $this->selectionProductsDisabledRequired->getChildProductIds($productId);
        return $productIdsDisabledRequired ? false : $result;
    }
}
