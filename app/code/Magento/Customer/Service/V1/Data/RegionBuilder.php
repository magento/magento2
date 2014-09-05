<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Customer\Service\V1\Data;

/**
 * Builder for the Region Service Data Object
 *
 * @method Region create()
 */
class RegionBuilder extends \Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder
{
    /**
     * Set region code
     *
     * @param string $regionCode
     * @return $this
     */
    public function setRegionCode($regionCode)
    {
        $this->_data[Region::KEY_REGION_CODE] = $regionCode;
        return $this;
    }

    /**
     * Set region
     *
     * @param string $regionName
     * @return $this
     */
    public function setRegion($regionName)
    {
        $this->_data[Region::KEY_REGION] = $regionName;
        return $this;
    }

    /**
     * Set region id
     *
     * @param string $regionId
     * @return $this
     */
    public function setRegionId($regionId)
    {
        $this->_data[Region::KEY_REGION_ID] = $regionId;
        return $this;
    }
}
