<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Sales\Api;

/**
 * Interface ShipmentManagementInterface
 */
interface ShipmentManagementInterface
{
    /**
     * Returns shipment label
     *
     * @param int $id
     * @return string
     */
    public function getLabel($id);

    /**
     * Returns list of comments attached to shipment
     * @param int $id
     * @return \Magento\Sales\Api\Data\ShipmentCommentSearchResultInterface
     */
    public function getCommentsList($id);

    /**
     * Notify user
     *
     * @param int $id
     * @return bool
     */
    public function notify($id);
}
