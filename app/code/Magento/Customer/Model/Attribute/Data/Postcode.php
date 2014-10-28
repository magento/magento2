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
namespace Magento\Customer\Model\Attribute\Data;

/**
 * Customer Address Postal/Zip Code Attribute Data Model
 * This Data Model Has to Be Set Up in additional EAV attribute table
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Postcode extends \Magento\Eav\Model\Attribute\Data\Text
{
    /**
     * Validate postal/zip code
     * Return true and skip validation if country zip code is optional
     *
     * @param array|string $value
     * @return boolean|array
     */
    /**
     * Directory data
     *
     * @var \Magento\Directory\Helper\Data
     */
    protected $_directoryData = null;

    /**
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Framework\Logger $logger
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param \Magento\Framework\Stdlib\String $stringHelper
     * @param \Magento\Directory\Helper\Data $directoryData
     */
    public function __construct(
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Logger $logger,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\Stdlib\String $stringHelper,
        \Magento\Directory\Helper\Data $directoryData
    ) {
        $this->_directoryData = $directoryData;
        parent::__construct($localeDate, $logger, $localeResolver, $stringHelper);
    }

    /**
     * @param string $value
     * @return true|string[]
     */
    public function validateValue($value)
    {
        $countryId = $this->getExtractedData('country_id');
        $optionalZip = $this->_directoryData->getCountriesWithOptionalZip();
        if (!in_array($countryId, $optionalZip)) {
            return parent::validateValue($value);
        }
        return true;
    }
}
