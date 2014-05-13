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
namespace Magento\Eav\Model\Attribute\Data;

use Magento\Framework\App\RequestInterface;

/**
 * EAV Entity Attribute Date Data Model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Date extends \Magento\Eav\Model\Attribute\Data\AbstractData
{
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
     * Validate data
     * Return true or array of errors
     *
     * @param array|string $value
     * @return bool|array
     */
    public function validateValue($value)
    {
        $errors = array();
        $attribute = $this->getAttribute();
        $label = $attribute->getStoreLabel();

        if ($value === false) {
            // try to load original value and validate it
            $value = $this->getEntity()->getDataUsingMethod($attribute->getAttributeCode());
        }

        if ($attribute->getIsRequired() && empty($value)) {
            $errors[] = __('"%1" is a required value.', $label);
        }

        if (!$errors && !$attribute->getIsRequired() && empty($value)) {
            return true;
        }

        $result = $this->_validateInputRule($value);
        if ($result !== true) {
            $errors = array_merge($errors, $result);
        }

        //range validation
        $validateRules = $attribute->getValidateRules();
        if (!empty($validateRules['date_range_min']) && strtotime(
            $value
        ) < $validateRules['date_range_min'] || !empty($validateRules['date_range_max']) && strtotime(
            $value
        ) > $validateRules['date_range_max']
        ) {
            if (!empty($validateRules['date_range_min']) && !empty($validateRules['date_range_max'])) {
                $errors[] = __(
                    'Please enter a valid date between %1 and %2 at %3.',
                    date('d/m/Y', $validateRules['date_range_min']),
                    date('d/m/Y', $validateRules['date_range_max']),
                    $label
                );
            } elseif (!empty($validateRules['date_range_min'])) {
                $errors[] = __(
                    'Please enter a valid date equal to or greater than %1 at %2.',
                    date('d/m/Y', $validateRules['date_range_min']),
                    $label
                );
            } elseif (!empty($validateRules['date_range_max'])) {
                $errors[] = __(
                    'Please enter a valid date less than or equal to %1 at %2.',
                    date('d/m/Y', $validateRules['date_range_max']),
                    $label
                );
            }
        }

        if (count($errors) == 0) {
            return true;
        }

        return $errors;
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
            if (empty($value)) {
                $value = null;
            }
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
    public function outputValue($format = \Magento\Eav\Model\AttributeDataFactory::OUTPUT_FORMAT_TEXT)
    {
        $value = $this->getEntity()->getData($this->getAttribute()->getAttributeCode());
        if ($value) {
            switch ($format) {
                case \Magento\Eav\Model\AttributeDataFactory::OUTPUT_FORMAT_TEXT:
                case \Magento\Eav\Model\AttributeDataFactory::OUTPUT_FORMAT_HTML:
                case \Magento\Eav\Model\AttributeDataFactory::OUTPUT_FORMAT_PDF:
                    $this->_dateFilterFormat(\Magento\Framework\Stdlib\DateTime\TimezoneInterface::FORMAT_TYPE_MEDIUM);
                    break;
            }
            $value = $this->_applyOutputFilter($value);
        }

        $this->_dateFilterFormat(\Magento\Framework\Stdlib\DateTime\TimezoneInterface::FORMAT_TYPE_SHORT);

        return $value;
    }
}
