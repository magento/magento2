<?php

namespace Magento\Wishlist\Api\data;

interface WishlistInterface
{
    /**#@+
     * Constants defined for keys of  data array
     */
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
     * Get if wishlist is shared
     *
     * @return int
     */
    public function getShared();

    /**
     * @param int $amount
     * @return mixed
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
     * @return $thisc
     */
    public function setSharingCode(string $code);

    /**
     * Get wishlist customer id
     *
     * @return int
     */
    public function getCustomerId();

    /**
     * Get wishlist last updated time
     * @return string|null
     */
    public function getUpdatedAt();

    /**
     * @param string $datetime
     * @return $this
     */
    public function setUpdatedAt($datetime);

}
