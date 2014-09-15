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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Customer\Model\Metadata\Form;

use Magento\Framework\Service\ArrayObjectSearch;

class Text extends AbstractData
{
    /**
     * @var \Magento\Framework\Stdlib\String
     */
    protected $_string;

    /**
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Framework\Logger $logger
     * @param \Magento\Customer\Service\V1\Data\Eav\AttributeMetadata $attribute
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param string $value
     * @param string $entityTypeCode
     * @param bool $isAjax
     * @param \Magento\Framework\Stdlib\String $stringHelper
     */
    public function __construct(
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Logger $logger,
        \Magento\Customer\Service\V1\Data\Eav\AttributeMetadata $attribute,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        $value,
        $entityTypeCode,
        $isAjax,
        \Magento\Framework\Stdlib\String $stringHelper
    ) {
        parent::__construct($localeDate, $logger, $attribute, $localeResolver, $value, $entityTypeCode, $isAjax);
        $this->_string = $stringHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function extractValue(\Magento\Framework\App\RequestInterface $request)
    {
        return $this->_applyInputFilter($this->_getRequestValue($request));
    }

    /**
     * {@inheritdoc}
     */
    public function validateValue($value)
    {
        $errors = array();
        $attribute = $this->getAttribute();
        $label = __($attribute->getStoreLabel());

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

        $minTextLength = ArrayObjectSearch::getArrayElementByName(
            $validateRules,
            'min_text_length'
        );
        if (!is_null($minTextLength) && $length < $minTextLength) {
            $errors[] = __('"%1" length must be equal or greater than %2 characters.', $label, $minTextLength);
        }

        $maxTextLength = ArrayObjectSearch::getArrayElementByName(
            $validateRules,
            'max_text_length'
        );
        if (!is_null($maxTextLength) && $length > $maxTextLength) {
            $errors[] = __('"%1" length must be equal or less than %2 characters.', $label, $maxTextLength);
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
     * {@inheritdoc}
     */
    public function compactValue($value)
    {
        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function restoreValue($value)
    {
        return $this->compactValue($value);
    }

    /**
     * {@inheritdoc}
     */
    public function outputValue($format = \Magento\Customer\Model\Metadata\ElementFactory::OUTPUT_FORMAT_TEXT)
    {
        return $this->_applyOutputFilter($this->_value);
    }
}
