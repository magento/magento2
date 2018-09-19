<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Api\Data;

/**
 * Represents Data Object for a Product Frontend Action like Product View or Comparison
 *
 * @api
 * @since 101.1.0
 */
interface ProductFrontendActionInterface
{
    /**
     * Gets Identifier of a Product Frontend Action
     *
     * @return int
     * @since 101.1.0
     */
    public function getActionId();

    /**
     * Sets Identifier of a Product Frontend Action
     *
     * @param int $actionId
     * @return void
     * @since 101.1.0
     */
    public function setActionId($actionId);

    /**
     * Gets Identifier of Visitor who performs a Product Frontend Action
     *
     * @return int
     * @since 101.1.0
     */
    public function getVisitorId();

    /**
     * Sets Identifier of Visitor who performs a Product Frontend Action
     *
     * @param int $visitorId
     * @return void
     * @since 101.1.0
     */
    public function setVisitorId($visitorId);

    /**
     * Gets Identifier of Customer who performs a Product Frontend Action
     *
     * @return int
     * @since 101.1.0
     */
    public function getCustomerId();

    /**
     * Sets Identifier of Customer who performs Product Frontend Action
     *
     * @param int $customerId
     * @return void
     * @since 101.1.0
     */
    public function setCustomerId($customerId);

    /**
     * Gets Identifier of Product a Product Frontend Action is performed on
     *
     * @return int
     * @since 101.1.0
     */
    public function getProductId();

    /**
     * Sets Identifier of Product a Product Frontend Action is performed on
     *
     * @param int $productId
     * @return void
     * @since 101.1.0
     */
    public function setProductId($productId);

    /**
     * Gets Identifier of Type of a Product Frontend Action
     *
     * @return string
     * @since 101.1.0
     */
    public function getTypeId();

    /**
     * Sets Identifier of Type of a Product Frontend Action
     *
     * @param string $typeId
     * @return void
     * @since 101.1.0
     */
    public function setTypeId($typeId);

    /**
     * Gets JS timestamp of a Product Frontend Action (in microseconds)
     *
     * @return int
     * @since 101.1.0
     */
    public function getAddedAt();

    /**
     * Sets JS timestamp of a Product Frontend Action (in microseconds)
     *
     * @param int $addedAt
     * @return void
     * @since 101.1.0
     */
    public function setAddedAt($addedAt);
}
