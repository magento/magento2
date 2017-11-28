<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\InventoryConfiguration\Model\SourceItemConfiguration;

use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\InventoryConfigurationApi\Api\Data\SourceItemConfigurationInterface;

/**
 * Delete SourceItem Configuration
 *
 * @api
 */
interface DeleteInterface
{
    /**
     * Delete the SourceItem Configuration data
     *
     * @param SourceItemConfigurationInterface $sourceItemConfiguration
     * @return void
     * @throws CouldNotDeleteException
     */
    public function delete(SourceItemConfigurationInterface $sourceItemConfiguration);
}
