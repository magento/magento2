<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Api;

/**
 * CMS Block management interface
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
    public function getByIdentifier(string $identifier, $storeId = null) : \Magento\Cms\Api\Data\BlockInterface;
}
