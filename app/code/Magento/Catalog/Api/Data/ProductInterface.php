<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Api\Data;

interface ProductInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**#@+
     * Constants defined for keys of  data array
     */
    const SKU = 'sku';

    const NAME = 'name';

    const PRICE = 'price';

    const WEIGHT = 'weight';

    const STATUS = 'status';

    const VISIBILITY = 'visibility';

    const ATTRIBUTE_SET_ID = 'attribute_set_id';

    const TYPE_ID = 'type_id';

    const CREATED_AT = 'created_at';

    const UPDATED_AT = 'updated_at';

    const STORE_ID = 'store_id';
    /**#@-*/

    /**
     * Product id
     *
     * @return int|null
     */
    public function getId();

    /**
     * Product sku
     *
     * @return string
     */
    public function getSku();

    /**
     * Product name
     *
     * @return string|null
     */
    public function getName();

    /**
     * Product store id
     *
     * @return int|null
     */
    public function getStoreId();

    /**
     * Product attribute set id
     *
     * @return int|null
     */
    public function getAttributeSetId();

    /**
     * Product price
     *
     * @return float|null
     */
    public function getPrice();

    /**
     * Product status
     *
     * @return int|null
     */
    public function getStatus();

    /**
     * Product visibility
     *
     * @return int|null
     */
    public function getVisibility();

    /**
     * Product type id
     *
     * @return string|null
     */
    public function getTypeId();

    /**
     * Product created date
     *
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * Product updated date
     *
     * @return string|null
     */
    public function getUpdatedAt();

    /**
     * Product weight
     *
     * @return float|null
     */
    public function getWeight();
}
