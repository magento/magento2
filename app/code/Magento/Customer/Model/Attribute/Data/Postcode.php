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

use Magento\Eav\Model\AttributeDataFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Logger;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface as MagentoTimezone;
use Magento\Directory\Helper\Data as DirectoryHelper;

/**
 * Customer Address Postal/Zip Code Attribute Data Model
 * This Data Model Has to Be Set Up in additional EAV attribute table
 */
class Postcode extends \Magento\Eav\Model\Attribute\Data\AbstractData
{
    /**
     * @var \Magento\Directory\Helper\Data
     */
    protected $directoryHelper;

    /**
     * @param MagentoTimezone $localeDate
     * @param Logger $logger
     * @param ResolverInterface $localeResolver
     * @param DirectoryHelper $directoryHelper
     */
    public function __construct(
        MagentoTimezone $localeDate,
        Logger $logger,
        ResolverInterface $localeResolver,
        DirectoryHelper $directoryHelper
    ) {
        $this->directoryHelper = $directoryHelper;
        parent::__construct($localeDate, $logger, $localeResolver);
    }

    /**
     * Validate postal/zip code
     * Return true and skip validation if country zip code is optional
     *
     * @param array|string $value
     * @return array|bool
     */
    public function validateValue($value)
    {
        $attribute = $this->getAttribute();
        $label = __($attribute->getStoreLabel());

        $countryId = $this->getExtractedData('country_id');
        if ($this->directoryHelper->isZipCodeOptional($countryId)) {
            return true;
        }

        $errors = [];
        if (empty($value) && $value !== '0') {
            $errors[] = __('"%1" is a required value.', $label);
        }
        if (count($errors) == 0) {
            return true;
        }
        return $errors;
    }

    /**
     * Extract data from request and return value
     *
     * @param RequestInterface $request
     * @return array|string
     */
    public function extractValue(RequestInterface $request)
    {
        $value = $this->_getRequestValue($request);
        return $this->_applyInputFilter($value);
    }

    /**
     * Export attribute value to entity model
     *
     * @param array|string $value
     * @return $this
     */
    public function compactValue($value)
    {
        if ($value !== false) {
            $this->getEntity()->setDataUsingMethod($this->getAttribute()->getAttributeCode(), $value);
        }
        return $this;
    }

    /**
     * Restore attribute value from SESSION to entity model
     *
     * @param array|string $value
     * @return $this
     */
    public function restoreValue($value)
    {
        return $this->compactValue($value);
    }

    /**
     * Return formated attribute value from entity model
     *
     * @param string $format
     * @return string|array
     */
    public function outputValue($format = AttributeDataFactory::OUTPUT_FORMAT_TEXT)
    {
        $value = $this->getEntity()
            ->getData($this->getAttribute()->getAttributeCode());
        $value = $this->_applyOutputFilter($value);
        return $value;
    }
}
