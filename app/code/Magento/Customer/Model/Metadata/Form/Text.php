<?php
/**
 * Form Element Text Data Model
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
 * @category    Magento
 * @package     Magento_Eav
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * EAV Entity Attribute Text Data Model
 *
 * @category    Magento
 * @package     Magento_Eav
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Customer\Model\Metadata\Form;

class Text extends AbstractData
{
    /**
     * @var \Magento\Core\Helper\String
     */
    protected $_string;

    /**
     * @param \Magento\Core\Model\LocaleInterface $locale
     * @param \Magento\Logger $logger
     * @param \Magento\Customer\Service\V1\Dto\Eav\AttributeMetadata $attribute
     * @param null $value
     * @param $entityTypeCode
     * @param bool $isAjax
     * @param \Magento\Stdlib\String $stringHelper
     */
    public function __construct(
        \Magento\Core\Model\LocaleInterface $locale,
        \Magento\Logger $logger,
        \Magento\Customer\Service\V1\Dto\Eav\AttributeMetadata $attribute,
        $value = null,
        $entityTypeCode,
        $isAjax = false,
        \Magento\Stdlib\String $stringHelper
    ) {
        parent::__construct($locale, $logger, $attribute, $value, $entityTypeCode, $isAjax);
        $this->_string = $stringHelper;
    }

    /**
     * Extract data from request and return value
     *
     * @param \Magento\App\RequestInterface $request
     * @return array|string
     */
    public function extractValue(\Magento\App\RequestInterface $request)
    {
        $value = $this->_getRequestValue($request);
        return $this->_applyInputFilter($value);
    }

    /**
     * Validate data
     * Return true or array of errors
     *
     * @param array|string $value
     * @return boolean|array
     */
    public function validateValue($value)
    {
        $errors     = array();
        $attribute  = $this->getAttribute();
        $label      = __($attribute->getStoreLabel());

        if ($value === false) {
            // try to load original value and validate it
            $value = $this->_value;
        }

        if ($attribute->isRequired() && empty($value) && $value !== '0') {
            $errors[] = __('"%1" is a required value.', $label);
        }

        if (!$errors && !$attribute->isRequired() && empty($value)) {
            return true;
        }

        // validate length
        $length = $this->_string->strlen(trim($value));

        $validateRules = $attribute->getValidationRules();
        if (!empty($validateRules['min_text_length']) && $length < $validateRules['min_text_length']) {
            $v = $validateRules['min_text_length'];
            $errors[] = __('"%1" length must be equal or greater than %2 characters.', $label, $v);
        }
        if (!empty($validateRules['max_text_length']) && $length > $validateRules['max_text_length']) {
            $v = $validateRules['max_text_length'];
            $errors[] = __('"%1" length must be equal or less than %2 characters.', $label, $v);
        }

        $result = $this->_validateInputRule($value);
        if ($result !== true) {
            $errors = array_merge($errors, $result);
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
     * @return string|value
     */
    public function compactValue($value)
    {
        if ($value !== false) {
            $value;
        }
        return false;
    }

    /**
     * Restore attribute value from SESSION to entity model
     *
     * @param array|string $value
     * @return string|value
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
        $value = $this->_value;
        $value = $this->_applyOutputFilter($value);

        return $value;
    }
}
