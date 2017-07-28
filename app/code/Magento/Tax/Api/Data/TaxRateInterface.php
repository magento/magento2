<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Api\Data;

/**
 * Tax rate interface.
 * @api
 * @since 2.0.0
 */
interface TaxRateInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * Get id
     *
     * @return int|null
     * @since 2.0.0
     */
    public function getId();

    /**
     * Set id
     *
     * @param int $id
     * @return $this
     * @since 2.0.0
     */
    public function setId($id);

    /**
     * Get country id
     *
     * @return string
     * @since 2.0.0
     */
    public function getTaxCountryId();

    /**
     * Set country id
     *
     * @param string $taxCountryId
     * @return $this
     * @since 2.0.0
     */
    public function setTaxCountryId($taxCountryId);

    /**
     * Get region id
     *
     * @return int|null
     * @since 2.0.0
     */
    public function getTaxRegionId();

    /**
     * Set region id
     *
     * @param int $taxRegionId
     * @return $this
     * @since 2.0.0
     */
    public function setTaxRegionId($taxRegionId);

    /**
     * Get region name
     *
     * @return string|null
     * @since 2.0.0
     */
    public function getRegionName();

    /**
     * Set region name
     *
     * @param string $regionName
     * @return $this
     * @since 2.0.0
     */
    public function setRegionName($regionName);

    /**
     * Get postcode
     *
     * @return string|null
     * @since 2.0.0
     */
    public function getTaxPostcode();

    /**
     * Set postcode
     *
     * @param string $taxPostCode
     * @return $this
     * @since 2.0.0
     */
    public function setTaxPostcode($taxPostCode);

    /**
     * Get zip is range
     *
     * @return int|null
     * @since 2.0.0
     */
    public function getZipIsRange();

    /**
     * Set zip is range
     *
     * @param int $zipIsRange
     * @return $this
     * @since 2.0.0
     */
    public function setZipIsRange($zipIsRange);

    /**
     * Get zip range from
     *
     * @return int|null
     * @since 2.0.0
     */
    public function getZipFrom();

    /**
     * Set zip range from
     *
     * @param int $zipFrom
     * @return $this
     * @since 2.0.0
     */
    public function setZipFrom($zipFrom);

    /**
     * Get zip range to
     *
     * @return int|null
     * @since 2.0.0
     */
    public function getZipTo();

    /**
     * Set zip range to
     *
     * @param int $zipTo
     * @return $this
     * @since 2.0.0
     */
    public function setZipTo($zipTo);

    /**
     * Get tax rate in percentage
     *
     * @return float
     * @since 2.0.0
     */
    public function getRate();

    /**
     * Set tax rate in percentage
     *
     * @param float $rate
     * @return $this
     * @since 2.0.0
     */
    public function setRate($rate);

    /**
     * Get tax rate code
     *
     * @return string
     * @since 2.0.0
     */
    public function getCode();

    /**
     * Set tax rate code
     *
     * @param string $code
     * @return $this
     * @since 2.0.0
     */
    public function setCode($code);

    /**
     * Get tax rate titles
     *
     * @return \Magento\Tax\Api\Data\TaxRateTitleInterface[]|null
     * @since 2.0.0
     */
    public function getTitles();

    /**
     * Set tax rate titles
     *
     * @param \Magento\Tax\Api\Data\TaxRateTitleInterface[] $titles
     * @return $this
     * @since 2.0.0
     */
    public function setTitles(array $titles = null);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Tax\Api\Data\TaxRateExtensionInterface|null
     * @since 2.0.0
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Tax\Api\Data\TaxRateExtensionInterface $extensionAttributes
     * @return $this
     * @since 2.0.0
     */
    public function setExtensionAttributes(\Magento\Tax\Api\Data\TaxRateExtensionInterface $extensionAttributes);
}
