<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Wonderland\Model\Data;

use Magento\Framework\Api\AbstractExtensibleObject;

class FakeRegion extends AbstractExtensibleObject
{
    /**#@+
     * Constants for keys of data array
     */
    const REGION_CODE = 'region_code';
    const REGION = 'region';
    const REGION_ID = 'region_id';
    /**#@-*/

    /**
     * Get region
     *
     * @return string
     */
    public function getRegion()
    {
        return $this->_get(self::REGION);
    }

    /**
     * Get region code
     *
     * @return string
     */
    public function getRegionCode()
    {
        return $this->_get(self::REGION_CODE);
    }

    /**
     * Get region id
     *
     * @return int
     */
    public function getRegionId()
    {
        return $this->_get(self::REGION_ID);
    }
}
