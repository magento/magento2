<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\WebapiAsync\Api\Data\AsyncResponse;

/**
 * AsyncResponseItemListInterface interface
 * List of status requested entities to async router. Accepted|Rejected
 * Temporary data list for async router response.
 *
 * @api
 * @since 100.3.0
 */
interface ItemsListInterface
{
    /**
     * Get list of statuses for requested entities.
     *
     * @return \Magento\WebapiAsync\Api\Data\AsyncResponse\ItemStatusInterface[]
     * @since 100.3.0
     */
    public function getItems();

    /**
     * @param $items \Magento\WebapiAsync\Api\Data\AsyncResponse\ItemStatusInterface[]
     * @return \Magento\WebapiAsync\Api\Data\AsyncResponse\ItemsListInterface
     */
    public function setItems($items);

    /**
     * @return string
     */
    public function getGroupId();

    /**
     * @param $groupId string
     * @return \Magento\WebapiAsync\Api\Data\AsyncResponse\ItemsListInterface
     */
    public function setGroupId($groupId);

    /**
     * @param bool $isErrors
     * @return \Magento\WebapiAsync\Api\Data\AsyncResponse\ItemsListInterface
     */
    public function setIsErrors($isErrors = false);

    /**
     * Is there errors during processing bulk
     *
     * @return boolean
     */
    public function getIsErrors();
}
