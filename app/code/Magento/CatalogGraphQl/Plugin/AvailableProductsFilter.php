<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Plugin;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status as ProductStatus;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\GraphQl\Model\Query\ContextFactoryInterface;

class AvailableProductsFilter
{
    /**
     * @var ContextFactoryInterface
     */
    private $contextFactory;

    /**
     * @param ContextFactoryInterface $contextFactory
     */
    public function __construct(ContextFactoryInterface $contextFactory)
    {
        $this->contextFactory = $contextFactory;
    }

    /**
     * Check that product is available.
     *
     * @param ProductRepositoryInterface $subject
     * @param ProductInterface $result
     * @param string $sku
     * @param bool $editMode
     * @param int|null $storeId
     * @param bool $forceReload
     * @return ProductInterface
     * @throws NoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGet(
        ProductRepositoryInterface $subject,
        ProductInterface $result,
        $sku,
        $editMode = false,
        $storeId = null,
        $forceReload = false
    ): ProductInterface {
        if (ProductStatus::STATUS_ENABLED !== (int) $result->getStatus()) {
            throw new NoSuchEntityException(
                __("The product that was requested doesn't exist. Verify the product and try again.")
            );
        }

        $context = $this->contextFactory->get();
        $store = $context->getExtensionAttributes()->getStore();
        if (!in_array($store->getWebsiteId(), $result->getWebsiteIds())) {
            throw new NoSuchEntityException(
                __("The product that was requested doesn't exist. Verify the product and try again.")
            );
        }

        return $result;
    }
}
