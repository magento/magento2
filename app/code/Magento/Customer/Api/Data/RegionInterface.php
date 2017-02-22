<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Customer address region interface.
 */
interface RegionInterface extends ExtensibleDataInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the getters in snake case
     */
    const REGION_CODE = 'region_code';
    const REGION = 'region';
    const REGION_ID = 'region_id';
    /**#@-*/

    /**
     * Get region code
     *
     * @api
     * @return string
     */
    public function getRegionCode();

    /**
     * Set region code
     *
     * @api
     * @param string $regionCode
     * @return $this
     */
    public function setRegionCode($regionCode);

    /**
     * Get region
     *
     * @api
     * @return string
     */
    public function getRegion();

    /**
     * Set region
     *
     * @api
     * @param string $region
     * @return $this
     */
    public function setRegion($region);

    /**
     * Get region id
     *
     * @api
     * @return int
     */
    public function getRegionId();

    /**
     * Set region id
     *
     * @api
     * @param int $regionId
     * @return $this
     */
    public function setRegionId($regionId);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @api
     * @return \Magento\Customer\Api\Data\RegionExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @api
     * @param \Magento\Customer\Api\Data\RegionExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(\Magento\Customer\Api\Data\RegionExtensionInterface $extensionAttributes);
}
