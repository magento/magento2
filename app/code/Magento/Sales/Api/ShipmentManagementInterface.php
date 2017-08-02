<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Api;

/**
 * Shipment management interface.
 *
 * A shipment is a delivery package that contains products. A shipment document accompanies the shipment. This
 * document lists the products and their quantities in the delivery package.
 * @api
 * @since 2.0.0
 */
interface ShipmentManagementInterface
{
    /**
     * Gets a specified shipment label.
     *
     * @param int $id The shipment label ID.
     * @return string Shipment label.
     * @since 2.0.0
     */
    public function getLabel($id);

    /**
     * Lists comments for a specified shipment.
     *
     * @param int $id The shipment ID.
     * @return \Magento\Sales\Api\Data\ShipmentCommentSearchResultInterface Shipment comment search result interface.
     * @since 2.0.0
     */
    public function getCommentsList($id);

    /**
     * Emails user a specified shipment.
     *
     * @param int $id The shipment ID.
     * @return bool
     * @since 2.0.0
     */
    public function notify($id);
}
