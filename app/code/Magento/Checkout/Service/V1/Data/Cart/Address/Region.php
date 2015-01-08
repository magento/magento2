<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Checkout\Service\V1\Data\Cart\Address;

/**
 * @codeCoverageIgnore
 */
class Region extends \Magento\Framework\Api\AbstractExtensibleObject
{
    /**#@+
     * Array keys
     */
    /**
     * Region code.
     */
    const REGION_CODE = 'region_code';

    /**
     * Region name.
     */
    const REGION = 'region';

    /**
     * Region ID.
     */
    const REGION_ID = 'region_id';

    /**#@-*/

    /**
     * Returns the region code.
     *
     * @return string Region code.
     */
    public function getRegionCode()
    {
        return $this->_get(self::REGION_CODE);
    }

    /**
     * Returns the region name.
     *
     * @return string Region.
     */
    public function getRegion()
    {
        return $this->_get(self::REGION);
    }

    /**
     * Returns the region ID.
     *
     * @return int Region ID.
     */
    public function getRegionId()
    {
        return $this->_get(self::REGION_ID);
    }
}
