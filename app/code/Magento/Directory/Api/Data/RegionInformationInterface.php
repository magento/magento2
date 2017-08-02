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
 * @since 2.0.0
 */
interface RegionInformationInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * Get region id
     *
     * @return string
     * @since 2.0.0
     */
    public function getId();

    /**
     * Set region id
     *
     * @param string $regionId
     * @return $this
     * @since 2.0.0
     */
    public function setId($regionId);

    /**
     * Get region code
     *
     * @return string
     * @since 2.0.0
     */
    public function getCode();

    /**
     * Set region code
     *
     * @param string $regionCode
     * @return $this
     * @since 2.0.0
     */
    public function setCode($regionCode);

    /**
     * Get region name
     *
     * @return string
     * @since 2.0.0
     */
    public function getName();

    /**
     * Set region name
     *
     * @param string $region
     * @return $this
     * @since 2.0.0
     */
    public function setName($regionName);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Directory\Api\Data\RegionInformationExtensionInterface|null
     * @since 2.0.0
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Directory\Api\Data\RegionInformationExtensionInterface $extensionAttributes
     * @return $this
     * @since 2.0.0
     */
    public function setExtensionAttributes(
        \Magento\Directory\Api\Data\RegionInformationExtensionInterface $extensionAttributes
    );
}
