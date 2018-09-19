<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Directory\Api\Data;

/**
 * Region Information interface.
 *
 * @api
 * @since 100.0.2
 */
interface RegionInformationInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * Get region id
     *
     * @return string
     */
    public function getId();

    /**
     * Set region id
     *
     * @param string $regionId
     * @return $this
     */
    public function setId($regionId);

    /**
     * Get region code
     *
     * @return string
     */
    public function getCode();

    /**
     * Set region code
     *
     * @param string $regionCode
     * @return $this
     */
    public function setCode($regionCode);

    /**
     * Get region name
     *
     * @return string
     */
    public function getName();

    /**
     * Set region name
     *
     * @param string $region
     * @return $this
     */
    public function setName($regionName);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Directory\Api\Data\RegionInformationExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Directory\Api\Data\RegionInformationExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\Directory\Api\Data\RegionInformationExtensionInterface $extensionAttributes
    );
}
