<?php

namespace Magento\Wishlist\Api\Data;

interface ItemInterface
{
    /**#@+
     * Constants defined for keys of the data array. Identical to the name of the getter in snake case
     */
    const WISHLIST_ITEM_ID = 'wishlist_item_id';
    const WISHLIST_ID = 'wishlist_id';
    const PRODUCT_ID = 'product_id';
    const STORE_ID = 'store_id';
    const ADDED_AT = 'added_at';
    const DESCRIPTION = 'description';
    const QTY = 'qty';

    /**#@-*/

    /**
     * Return wishlist item id
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set wishlist item id
     * @param int $id
     * @return $this
     */
    public function setId($id);

    /**
     * Get wishlist id
     *
     * @return int|null
     */
    public function getWishlistId();

    /**
     * Set wishlist item id
     *
     * @param int $id
     * @return mixed
     */
    public function setWishlistId($id);

    /**
     * Get product id
     *
     * @return int|null
     */
    public function getProductId();

    /**
     * @param $id
     * @return mixed
     */
    public function setProductId($id);

    /**
     * @return mixed
     */
    public function getStoreId();

    /**
     * @param int $id
     * @return mixed
     */
    public function setStoreId($id);


    /**
     * @return string|null
     */
    public function getAddedAt();

    /**
     * @param string $datetime
     * @return mixed
     */
    public function setAddedAt($datetime);

    /**
     * @return mixed
     */
    public function getDescription();

    /**
     * @param string $description
     * @return mixed
     */
    public function setDescription($description);

    /**
     * Get quantity
     *
     * @return int|null
     */
    public function getQty();

    /**
     * Set quantity. If quantity is less than 0 - set it to 1
     *
     * @param int $qty
     * @return $this
     */
    public function setQty($qty);


}
