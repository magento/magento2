<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Directory\Api\Data;

/**
 * Region Information interface.
 *
 * @api
 */
interface RegionInformationInterface
{
    /**
     * Get region id
     *
     * @api
     * @return string
     */
    public function getId();

    /**
     * Set region id
     *
     * @api
     * @param string $regionId
     * @return $this
     */
    public function setId($regionId);

    /**
     * Get region code
     *
     * @api
     * @return string
     */
    public function getCode();

    /**
     * Set region code
     *
     * @api
     * @param string $regionCode
     * @return $this
     */
    public function setCode($regionCode);

    /**
     * Get region name
     *
     * @api
     * @return string
     */
    public function getName();

    /**
     * Set region name
     *
     * @api
     * @param string $region
     * @return $this
     */
    public function setName($regionName);

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
