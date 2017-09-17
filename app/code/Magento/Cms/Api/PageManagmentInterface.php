<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Api;

/**
 * CMS Page management interface
 * @api
 */

interface PageManagmentInterface
{
    /**
     * Load page data by given page identifier.
     *
     * @param string $identifier
     * @param int|null $storeId
     * @return \Magento\Cms\Api\Data\PageInterface
     */
    public function getByIdentifier(string $identifier, $storeId = null) : \Magento\Cms\Api\Data\PageInterface;
}