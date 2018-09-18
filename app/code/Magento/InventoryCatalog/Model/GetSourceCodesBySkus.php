<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Model;

use Magento\Catalog\Model\ResourceModel\Product as ProductResourceModel;
use Magento\InventoryCatalogApi\Model\GetSourceCodesBySkusInterface;

/**
 * @inheritdoc
 */
class GetSourceCodesBySkus implements GetSourceCodesBySkusInterface
{
    /**
     * @var ProductResourceModel
     */
    private $productResource;

    /**
     * @param ResourceModel\GetSourcesCodesBySkus $getSourcesCodesBySkus
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        ResourceModel\GetSourcesCodesBySkus $getSourcesCodesBySkus
    ) {
        $this->productResource = $getSourcesCodesBySkus;
    }

    /**
     * @inheritdoc
     */
    public function execute(array $skus): array
    {
        return $this->productResource->execute($skus);
    }
}
