<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Sales\Api\Data;

/**
 * Interface ShipmentItemInterface
 */
interface ShipmentItemInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const ENTITY_ID = 'entity_id';
    const PARENT_ID = 'parent_id';
    const ROW_TOTAL = 'row_total';
    const PRICE = 'price';
    const WEIGHT = 'weight';
    const QTY = 'qty';
    const PRODUCT_ID = 'product_id';
    const ORDER_ITEM_ID = 'order_item_id';
    const ADDITIONAL_DATA = 'additional_data';
    const DESCRIPTION = 'description';
    const NAME = 'name';
    const SKU = 'sku';

    /**
     * Returns additional_data
     *
     * @return string
     */
    public function getAdditionalData();

    /**
     * Returns description
     *
     * @return string
     */
    public function getDescription();

    /**
     * Returns entity_id
     *
     * @return int
     */
    public function getEntityId();

    /**
     * Returns name
     *
     * @return string
     */
    public function getName();

    /**
     * Returns order_item_id
     *
     * @return int
     */
    public function getOrderItemId();

    /**
     * Returns parent_id
     *
     * @return int
     */
    public function getParentId();

    /**
     * Returns price
     *
     * @return float
     */
    public function getPrice();

    /**
     * Returns product_id
     *
     * @return int
     */
    public function getProductId();

    /**
     * Returns qty
     *
     * @return float
     */
    public function getQty();

    /**
     * Returns row_total
     *
     * @return float
     */
    public function getRowTotal();

    /**
     * Returns sku
     *
     * @return string
     */
    public function getSku();

    /**
     * Returns weight
     *
     * @return float
     */
    public function getWeight();
}
