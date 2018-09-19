<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Model;

use Magento\InventoryApi\Model\GetSourceCodesBySkusInterface;

/**
 * @inheritdoc
 */
class GetSourceCodesBySkus implements GetSourceCodesBySkusInterface
{
    /**
     * @var ResourceModel\GetSourceCodesBySkus
     */
    private $getSourceCodesBySkus;

    /**
     * @param ResourceModel\GetSourceCodesBySkus $getSourceCodesBySkus
     */
    public function __construct(ResourceModel\GetSourceCodesBySkus $getSourceCodesBySkus)
    {
        $this->getSourceCodesBySkus = $getSourceCodesBySkus;
    }

    /**
     * @param array $skus
     * @return array
     */
    public function execute(array $skus): array
    {
        return $this->getSourceCodesBySkus->execute($skus);
    }
}
