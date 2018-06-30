<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Wishlist\Api\Data;

interface WishlistInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**#@+
     * Constants defined for keys of  data array
     */
    const WISHLIST_ID = 'wishlist_id';

    const DEFAULT_WISHLIST_NAME = 'wishlist';

    const NAME = 'name';

    const SHARING_CODE = 'sharing_code';

    const CUSTOMER_ID = 'customer_id';

    const SHARED = 'shared';

    /**#@-*/

    /**
     * Get wishlist id
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set wishlist id
     * @param int $id
     * @return $this
     */
    public function setId($id);

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
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Wishlist\Api\Data\WishlistExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Wishlist\Api\Data\WishlistExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(\Magento\Wishlist\Api\Data\WishlistExtensionInterface $extensionAttributes);


}
