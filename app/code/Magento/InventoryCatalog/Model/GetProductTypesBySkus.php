<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Model;

use Magento\InventoryCatalog\Model\ResourceModel\GetProductTypesBySkus as GetProductTypesBySkusResourceModel;

/**
 * @inheritdoc
 */
class GetProductTypesBySkus implements GetProductTypesBySkusInterface
{
    /**
     * @var GetProductTypesBySkusResourceModel
     */
    private $getProductTypesBySkusResource;

    /**
     * @param GetProductTypesBySkusResourceModel $getProductTypesBySkusResource
     */
    public function __construct(
        GetProductTypesBySkusResourceModel $getProductTypesBySkusResource
    ) {
        $this->getProductTypesBySkusResource = $getProductTypesBySkusResource;
    }

    /**
     * @inheritdoc
     */
    public function execute(array $skus)
    {
        $typesBySkus = $this->getProductTypesBySkusResource->execute($skus);
        $notFoundedSkus = array_diff($skus, array_keys($typesBySkus));

        if (!empty($notFoundedSkus)) {
            throw new \Magento\Framework\Exception\InputException(
                __('Following products with requested skus were not found: %1', implode($notFoundedSkus, ', '))
            );
        }

        return $typesBySkus;
    }
}
