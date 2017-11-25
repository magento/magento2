<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfigurationApi\Api;

use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\InventoryConfigurationApi\Api\Data\SourceItemConfigurationInterface;

/**
 * Get the source configuration for a product.
 *
 * Used fully qualified namespaces in annotations for proper work of WebApi request parser
 *
 * @api
 */
interface DeleteSourceItemConfigurationInterface
{
    /**
     * Delete the SourceItem Configuration data
     *
     * @param SourceItemConfigurationInterface $sourceItemConfiguration
     * @return void
     * @throws CouldNotDeleteException
     */
    public function execute(SourceItemConfigurationInterface $sourceItemConfiguration);
}
