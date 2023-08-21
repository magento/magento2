<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Customer group website interface for websites that are excluded from customer group.
 * @api
 */
interface GroupExcludedWebsiteInterface extends ExtensibleDataInterface
{
    /**#@+
     * Constants for keys of data array
     */
    public const ID = 'entity_id';
    public const GROUP_ID = 'customer_group_id';
    public const WEBSITE_ID = 'website_id';
    /**#@-*/

    /**
     * Get entity id
     *
     * @return int|null
     */
    public function getGroupWebsiteId(): ?int;

    /**
     * Set entity id
     *
     * @param int $id
     * @return $this
     */
    public function setGroupWebsiteId(int $id): GroupExcludedWebsiteInterface;

    /**
     * Get customer group id
     *
     * @return int|null
     */
    public function getGroupId(): ?int;

    /**
     * Set customer group id
     *
     * @param int $id
     * @return $this
     */
    public function setGroupId(int $id): GroupExcludedWebsiteInterface;

    /**
     * Get excluded website id
     *
     * @return int|null
     */
    public function getExcludedWebsiteId(): ?int;

    /**
     * Set excluded website id
     *
     * @param int $websiteId
     * @return $this
     */
    public function setExcludedWebsiteId(int $websiteId): GroupExcludedWebsiteInterface;

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Customer\Api\Data\GroupExcludedWebsiteExtensionInterface|null
     */
    public function getExtensionAttributes(): ?GroupExcludedWebsiteExtensionInterface;

    /**
     * Set an extension attributes object.
     *
     * @param GroupExcludedWebsiteExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        GroupExcludedWebsiteExtensionInterface $extensionAttributes
    ): GroupExcludedWebsiteInterface;
}
