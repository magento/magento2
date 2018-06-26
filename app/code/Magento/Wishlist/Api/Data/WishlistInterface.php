<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Wishlist\Api\Data;

interface WishlistInterface
{
    /**#@+
     * Constants defined for keys of  data array
     */
    const WISHLIST_ID = 'wishlist_id';

    const NAME = 'name';

    const SHARING_CODE = 'sharing_code';

    const CUSTOMER_ID = 'customer_id';

    const SHARED = 'shared';

    const UPDATED_AT = 'updated_at';

    /**#@-*/

    /**
     * Get wishlist id
     *
     * @return int|null
     */
    public function getId();

    /**
     * Get wishlist name
     *
     * @return string|null
     */
    public function getName();

    /**
     * Get amount of times wishlist has been shared
     *
     * @return int
     */
    public function getShared();

    /**
     * Set amount of times wishlist has been shared
     *
     * @param int $amount
     * @return $this
     */
    public function setShared(int $amount);

    /**
     * Get wishlist sharing code
     *
     * @return string|null
     */
    public function getSharingCode();

    /**
     * Set wishlist sharing code
     *
     * @param string $code
     * @return $this
     */
    public function setSharingCode(string $code);

    /**
     * Get wishlist customer id
     *
     * @return int
     */
    public function getCustomerId();

    /**
     * Set wishlist customer id
     *
     * @return $this
     */
    public function setCustomerId($customerId);

    /**
     * Get wishlist last updated time
     *
     * @return string|null
     */
    public function getUpdatedAt();

    /**
     * Set wishlist last updated time
     *
     * @param string $datetime
     * @return $this
     */
    public function setUpdatedAt($datetime);

}
