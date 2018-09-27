<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Setup\Operation;

use Magento\Framework\App\ResourceConnection;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventoryCatalogApi\Api\DefaultSourceProviderInterface;

/**
 * Create default source during installation
 */
class CreateDefaultSource
{
    /**
     * @var DefaultSourceProviderInterface
     */
    private $defaultSourceProvider;

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @param DefaultSourceProviderInterface $defaultSourceProvider
     * @param ResourceConnection $resource
     */
    public function __construct(
        DefaultSourceProviderInterface $defaultSourceProvider,
        ResourceConnection $resource
    ) {
        $this->defaultSourceProvider = $defaultSourceProvider;
        $this->resource = $resource;
    }

    /**
     * Create default source
     *
     * @return void
     */
    public function execute()
    {
        $connection = $this->resource->getConnection();
        $sourceData = [
            SourceInterface::SOURCE_CODE => $this->defaultSourceProvider->getCode(),
            SourceInterface::NAME => 'Default Source',
            SourceInterface::ENABLED => 1,
            SourceInterface::DESCRIPTION => 'Default Source',
            SourceInterface::LATITUDE => 0,
            SourceInterface::LONGITUDE => 0,
            SourceInterface::COUNTRY_ID => 'US',
            SourceInterface::POSTCODE => '00000',
        ];
        $connection->insert($this->resource->getTableName('inventory_source'), $sourceData);
    }
}
