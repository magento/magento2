<?php

namespace Magento\Wishlist\Api\data;

interface WishlistInterface
{
    /**#@+
     * Constants defined for keys of  data array
     */
    const NAME = 'name';

    const SHARING_CODE = 'sharing_code';

    const ATTRIBUTES = [
        self::NAME,
    ];
    /**#@-*/

    /**
     * Get wishlist id
     *
     * @return int|null
     */
    public function getId();

    /**
     * Wishlist name
     *
     * @return string|null
     */
    public function getName();
}
