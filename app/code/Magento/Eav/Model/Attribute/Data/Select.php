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
 * EAV Entity Attribute Select Data Model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Select extends \Magento\Eav\Model\Attribute\Data\AbstractData
{
    /**
     * Extract data from request and return value
     *
     * @param RequestInterface $request
     * @return array|string
     */
    public function extractValue(RequestInterface $request)
    {
        return $this->_getRequestValue($request);
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
        $label = __($attribute->getStoreLabel());

        if ($value === false) {
            // try to load original value and validate it
            $value = $this->getEntity()->getData($attribute->getAttributeCode());
        }

        if ($attribute->getIsRequired() && empty($value) && $value != '0') {
            $errors[] = __('"%1" is a required value.', $label);
        }

        if (!$errors && !$attribute->getIsRequired() && empty($value)) {
            return true;
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
            $this->getEntity()->setData($this->getAttribute()->getAttributeCode(), $value);
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
     * Return a text for option value
     *
     * @param int $value
     * @return string
     */
    protected function _getOptionText($value)
    {
        return $this->getAttribute()->getSource()->getOptionText($value);
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
        switch ($format) {
            case \Magento\Eav\Model\AttributeDataFactory::OUTPUT_FORMAT_JSON:
                $output = $value;
                break;
            default:
                if ($value != '') {
                    $output = $this->_getOptionText($value);
                } else {
                    $output = '';
                }
                break;
        }

        return $output;
    }
}
