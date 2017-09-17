<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Api;

/**
 * @api
 */

interface BlockManagementInterface
{
    /**
     * Load block data by given block identifier.
     *
     * @param string $identifier
     * @param int|null $storeId
     * @return \Magento\Cms\Api\Data\BlockInterface
     */
    public function getByIdentifier($identifier, $storeId = null);
}