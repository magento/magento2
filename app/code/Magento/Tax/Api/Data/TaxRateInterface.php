<?php
/**
 *
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

namespace Magento\Tax\Api\Data;

interface TaxRateInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**#@+
     * Constants defined for keys of array, makes typos less likely
     */
    const KEY_ID = 'id';
    const KEY_COUNTRY_ID = 'tax_country_id';
    const KEY_REGION_ID = 'tax_region_id';
    const KEY_REGION_NAME = 'region_name';
    const KEY_POSTCODE = 'tax_postcode';
    const KEY_ZIP_RANGE_FROM = 'zip_from';
    const KEY_ZIP_RANGE_TO = 'zip_to';
    const KEY_PERCENTAGE_RATE = 'rate';
    const KEY_CODE = 'code';
    const KEY_TITLES = 'titles';
    /**#@-*/

    /**
     * Get id
     *
     * @return int|null
     */
    public function getId();

    /**
     * Get country id
     *
     * @return string
     */
    public function getTaxCountryId();

    /**
     * Get region id
     *
     * @return int|null
     */
    public function getTaxRegionId();

    /**
     * Get region name
     *
     * @return string|null
     */
    public function getRegionName();

    /**
     * Get postcode
     *
     * @return string|null
     */
    public function getTaxPostcode();

    /**
     * Get zip is range
     *
     * @return int|null
     */
    public function getZipIsRange();

    /**
     * Get zip range from
     *
     * @return int|null
     */
    public function getZipFrom();

    /**
     * Get zip range to
     *
     * @return int|null
     */
    public function getZipTo();

    /**
     * Get tax rate in percentage
     *
     * @return float
     */
    public function getRate();

    /**
     * Get tax rate code
     *
     * @return string
     */
    public function getCode();

    /**
     * Get tax rate titles
     *
     * @return \Magento\Tax\Api\Data\TaxRateTitleInterface[]|null
     */
    public function getTitles();
}
