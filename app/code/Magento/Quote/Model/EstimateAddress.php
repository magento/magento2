<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Model;

use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\Quote\Api\Data\EstimateAddressInterface;

/**
 * Class \Magento\Quote\Model\EstimateAddress
 *
 */
class EstimateAddress extends AbstractExtensibleModel implements EstimateAddressInterface
{
    /**
     * Get region name
     *
     * @return string
     */
    public function getRegion()
    {
        return $this->getData(self::KEY_REGION);
    }

    /**
     * Set region name
     *
     * @param string $region
     * @return $this
     */
    public function setRegion($region)
    {
        return $this->setData(self::KEY_REGION, $region);
    }

    /**
     * Get region id
     *
     * @return int
     */
    public function getRegionId()
    {
        return $this->getData(self::KEY_REGION_ID);
    }

    /**
     * Set region id
     *
     * @param int $regionId
     * @return $this
     */
    public function setRegionId($regionId)
    {
        return $this->setData(self::KEY_REGION_ID, $regionId);
    }

    /**
     * Get country id
     *
     * @return string
     */
    public function getCountryId()
    {
        return $this->getData(self::KEY_COUNTRY_ID);
    }

    /**
     * Set country id
     *
     * @param string $countryId
     * @return $this
     */
    public function setCountryId($countryId)
    {
        return $this->setData(self::KEY_COUNTRY_ID, $countryId);
    }

    /**
     * Get postcode
     *
     * @return string
     */
    public function getPostcode()
    {
        return $this->getData(self::KEY_POSTCODE);
    }

    /**
     * Set postcode
     *
     * @param string $postcode
     * @return $this
     */
    public function setPostcode($postcode)
    {
        return $this->setData(self::KEY_POSTCODE, $postcode);
    }

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Quote\Api\Data\EstimateAddressExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Quote\Api\Data\EstimateAddressExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\Quote\Api\Data\EstimateAddressExtensionInterface $extensionAttributes
    ) {
        $this->_setExtensionAttributes($extensionAttributes);
    }
}
