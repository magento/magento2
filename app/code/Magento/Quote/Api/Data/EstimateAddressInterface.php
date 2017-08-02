<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Api\Data;

/**
 * Interface EstimateAddressInterface
 * @api
 * @since 2.0.0
 */
interface EstimateAddressInterface extends \Magento\Framework\Api\CustomAttributesDataInterface
{
    /**#@+
     * Constants defined for keys of array, makes typos less likely
     */
    const KEY_COUNTRY_ID = 'country_id';

    const KEY_REGION_ID = 'region_id';

    const KEY_REGION = 'region';

    const KEY_POSTCODE = 'postcode';

    /**#@-*/

    /**
     * Get region name
     *
     * @return string
     * @since 2.0.0
     */
    public function getRegion();

    /**
     * Set region name
     *
     * @param string $region
     * @return $this
     * @since 2.0.0
     */
    public function setRegion($region);

    /**
     * Get region id
     *
     * @return int
     * @since 2.0.0
     */
    public function getRegionId();

    /**
     * Set region id
     *
     * @param int $regionId
     * @return $this
     * @since 2.0.0
     */
    public function setRegionId($regionId);

    /**
     * Get country id
     *
     * @return string
     * @since 2.0.0
     */
    public function getCountryId();

    /**
     * Set country id
     *
     * @param string $countryId
     * @return $this
     * @since 2.0.0
     */
    public function setCountryId($countryId);

    /**
     * Get postcode
     *
     * @return string
     * @since 2.0.0
     */
    public function getPostcode();

    /**
     * Set postcode
     *
     * @param string $postcode
     * @return $this
     * @since 2.0.0
     */
    public function setPostcode($postcode);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Quote\Api\Data\EstimateAddressExtensionInterface|null
     * @since 2.0.0
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Quote\Api\Data\EstimateAddressExtensionInterface $extensionAttributes
     * @return $this
     * @since 2.0.0
     */
    public function setExtensionAttributes(
        \Magento\Quote\Api\Data\EstimateAddressExtensionInterface $extensionAttributes
    );
}
