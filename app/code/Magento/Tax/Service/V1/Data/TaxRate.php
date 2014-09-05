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

namespace Magento\Tax\Service\V1\Data;

/**
 * Service data object for a tax percentage rate associated with a location.
 */
class TaxRate extends \Magento\Framework\Service\Data\AbstractExtensibleObject
{
    /**#@+
     * Constants defined for keys of array, makes typos less likely
     */
    const KEY_ID = 'id';
    const KEY_COUNTRY_ID = 'country_id';
    const KEY_REGION_ID = 'region_id';
    const KEY_REGION_NAME = 'region_name';
    const KEY_POSTCODE = 'postcode';
    const KEY_ZIP_RANGE = 'zip_range';
    const KEY_PERCENTAGE_RATE = 'percentage_rate';
    const KEY_CODE = 'code';
    const KEY_TITLES = 'titles';
    /**#@-*/

    /**
     * Get id
     *
     * @return int|null
     */
    public function getId()
    {
        return $this->_get(self::KEY_ID);
    }

    /**
     * Get country id
     *
     * @return string
     */
    public function getCountryId()
    {
        return $this->_get(self::KEY_COUNTRY_ID);
    }

    /**
     * Get region id
     *
     * @return int|null
     */
    public function getRegionId()
    {
        return $this->_get(self::KEY_REGION_ID);
    }

    /**
     * Get region name
     *
     * @return string|null
     */
    public function getRegionName()
    {
        return $this->_get(self::KEY_REGION_NAME);
    }

    /**
     * Get postcode
     *
     * @return string|null
     */
    public function getPostcode()
    {
        return $this->_get(self::KEY_POSTCODE);
    }

    /**
     * Get zip range
     *
     * @return \Magento\Tax\Service\V1\Data\ZipRange|null
     */
    public function getZipRange()
    {
        return $this->_get(self::KEY_ZIP_RANGE);
    }

    /**
     * Get tax rate in percentage
     *
     * @return float
     */
    public function getPercentageRate()
    {
        return $this->_get(self::KEY_PERCENTAGE_RATE);
    }

    /**
     * Get tax rate code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->_get(self::KEY_CODE);
    }

    /**
     * Get tax rate titles
     *
     * @return \Magento\Tax\Service\V1\Data\TaxRateTitle[]|null
     */
    public function getTitles()
    {
        return $this->_get(self::KEY_TITLES);
    }
}
