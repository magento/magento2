<?php
/**
 * Form Element Abstract Data Model
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Customer\Model\Metadata\Form;

use Magento\Framework\Api\ArrayObjectSearch;
use Magento\Framework\App\ObjectManager;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class AbstractData
{
    /**
     * Request Scope name
     *
     * @var string
     */
    protected $_requestScope;

    /**
     * Scope visibility flag
     *
     * @var boolean
     */
    protected $_requestScopeOnly = true;

    /**
     * Is AJAX request flag
     *
     * @var boolean
     */
    protected $_isAjax = false;

    /**
     * Array of full extracted data
     * Needed for depends attributes
     *
     * @var array
     */
    protected $_extractedData = [];

    /**
     * Date filter format
     *
     * @var string
     */
    protected $_dateFilterFormat;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_localeDate;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $_localeResolver;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * @var \Magento\Customer\Api\Data\AttributeMetadataInterface
     */
    protected $_attribute;

    /**
     * @var string|int|bool
     */
    protected $_value;

    /**
     * @var  string
     */
    protected $_entityTypeCode;

    /**
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Customer\Api\Data\AttributeMetadataInterface $attribute
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param string|int|bool $value
     * @param string $entityTypeCode
     * @param bool $isAjax
     */
    public function __construct(
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Customer\Api\Data\AttributeMetadataInterface $attribute,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        $value,
        $entityTypeCode,
        $isAjax = false
    ) {
        $this->_localeDate = $localeDate;
        $this->_logger = $logger;
        $this->_attribute = $attribute;
        $this->_localeResolver = $localeResolver;
        $this->_value = $value;
        $this->_entityTypeCode = $entityTypeCode;
        $this->_isAjax = $isAjax;
    }

    /**
     * Return Attribute instance
     *
     * @return \Magento\Customer\Api\Data\AttributeMetadataInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAttribute()
    {
        if (!$this->_attribute) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Attribute object is undefined'));
        }
        return $this->_attribute;
    }

    /**
     * Set Request scope
     *
     * @param string $scope
     * @return $this
     */
    public function setRequestScope($scope)
    {
        $this->_requestScope = $scope;
        return $this;
    }

    /**
     * Set scope visibility
     * Search value only in scope or search value in scope and global
     *
     * @param boolean $flag
     * @return $this
     */
    public function setRequestScopeOnly($flag)
    {
        $this->_requestScopeOnly = (bool)$flag;
        return $this;
    }

    /**
     * Set array of full extracted data
     *
     * @param array $data
     * @return $this
     */
    public function setExtractedData(array $data)
    {
        $this->_extractedData = $data;
        return $this;
    }

    /**
     * Return extracted data
     *
     * @param string $index
     * @return array|null
     */
    public function getExtractedData($index = null)
    {
        if (!is_null($index)) {
            if (isset($this->_extractedData[$index])) {
                return $this->_extractedData[$index];
            }
            return null;
        }
        return $this->_extractedData;
    }

    /**
     * Apply attribute input filter to value
     *
     * @param string $value
     * @return string|bool
     */
    protected function _applyInputFilter($value)
    {
        if ($value === false) {
            return false;
        }

        $filter = $this->_getFormFilter();
        if ($filter) {
            $value = $filter->inputFilter($value);
        }

        return $value;
    }

    /**
     * Return Data Form Input/Output Filter
     *
     * @return \Magento\Framework\Data\Form\Filter\FilterInterface|false
     */
    protected function _getFormFilter()
    {
        $filterCode = $this->getAttribute()->getInputFilter();
        if ($filterCode) {
            $filterClass = 'Magento\Framework\Data\Form\Filter\\' . ucfirst($filterCode);
            if ($filterCode == 'date') {
                $filter = new $filterClass($this->_dateFilterFormat(), $this->_localeResolver);
            } else {
                $filter = new $filterClass();
            }
            return $filter;
        }
        return false;
    }

    /**
     * Get/Set/Reset date filter format
     *
     * @param string|null|false $format
     * @return $this|string
     */
    protected function _dateFilterFormat($format = null)
    {
        if (is_null($format)) {
            // get format
            if (is_null($this->_dateFilterFormat)) {
                $this->_dateFilterFormat = \IntlDateFormatter::SHORT;
            }
            return $this->_localeDate->getDateFormat($this->_dateFilterFormat);
        } elseif ($format === false) {
            // reset value
            $this->_dateFilterFormat = null;
            return $this;
        }

        $this->_dateFilterFormat = $format;
        return $this;
    }

    /**
     * Apply attribute output filter to value
     *
     * @param string $value
     * @return string
     */
    protected function _applyOutputFilter($value)
    {
        $filter = $this->_getFormFilter();
        if ($filter) {
            $value = $filter->outputFilter($value);
        }

        return $value;
    }

    /**
     * Validate value by attribute input validation rule
     *
     * @param string $value
     * @return array|true
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _validateInputRule($value)
    {
        // skip validate empty value
        if (empty($value)) {
            return true;
        }

        $label = $this->getAttribute()->getStoreLabel();
        $validateRules = $this->getAttribute()->getValidationRules();

        $inputValidation = ArrayObjectSearch::getArrayElementByName(
            $validateRules,
            'input_validation'
        );

        if (!is_null($inputValidation)) {
            switch ($inputValidation) {
                case 'alphanumeric':
                    $validator = new \Zend_Validate_Alnum(true);
                    $validator->setMessage(__('"%1" invalid type entered.', $label), \Zend_Validate_Alnum::INVALID);
                    $validator->setMessage(
                        __('"%1" contains non-alphabetic or non-numeric characters.', $label),
                        \Zend_Validate_Alnum::NOT_ALNUM
                    );
                    $validator->setMessage(__('"%1" is an empty string.', $label), \Zend_Validate_Alnum::STRING_EMPTY);
                    if (!$validator->isValid($value)) {
                        return $validator->getMessages();
                    }
                    break;
                case 'numeric':
                    $validator = new \Zend_Validate_Digits();
                    $validator->setMessage(__('"%1" invalid type entered.', $label), \Zend_Validate_Digits::INVALID);
                    $validator->setMessage(
                        __('"%1" contains non-numeric characters.', $label),
                        \Zend_Validate_Digits::NOT_DIGITS
                    );
                    $validator->setMessage(
                        __('"%1" is an empty string.', $label),
                        \Zend_Validate_Digits::STRING_EMPTY
                    );
                    if (!$validator->isValid($value)) {
                        return $validator->getMessages();
                    }
                    break;
                case 'alpha':
                    $validator = new \Zend_Validate_Alpha(true);
                    $validator->setMessage(__('"%1" invalid type entered.', $label), \Zend_Validate_Alpha::INVALID);
                    $validator->setMessage(
                        __('"%1" contains non-alphabetic characters.', $label),
                        \Zend_Validate_Alpha::NOT_ALPHA
                    );
                    $validator->setMessage(__('"%1" is an empty string.', $label), \Zend_Validate_Alpha::STRING_EMPTY);
                    if (!$validator->isValid($value)) {
                        return $validator->getMessages();
                    }
                    break;
                case 'email':
                    /**
                    __('"%1" is not a valid email address.')
                    */
                    $validator = ObjectManager::getInstance()->get(\Magento\Framework\Validator\Email::class);
                    if (!$validator->isValid($value)) {
                        return [__('"%1" is not a valid email address.', $label)];
                    }
                    break;
                case 'url':
                    $parsedUrl = parse_url($value);
                    if ($parsedUrl === false || empty($parsedUrl['scheme']) || empty($parsedUrl['host'])) {
                        return [__('"%1" is not a valid URL.', $label)];
                    }
                    $validator = new \Zend_Validate_Hostname();
                    if (!$validator->isValid($parsedUrl['host'])) {
                        return [__('"%1" is not a valid URL.', $label)];
                    }
                    break;
                case 'date':
                    $validator = new \Zend_Validate_Date(\Magento\Framework\Stdlib\DateTime::DATE_INTERNAL_FORMAT);
                    $validator->setMessage(__('"%1" invalid type entered.', $label), \Zend_Validate_Date::INVALID);
                    $validator->setMessage(__('"%1" is not a valid date.', $label), \Zend_Validate_Date::INVALID_DATE);
                    $validator->setMessage(
                        __('"%1" does not fit the entered date format.', $label),
                        \Zend_Validate_Date::FALSEFORMAT
                    );
                    if (!$validator->isValid($value)) {
                        return array_unique($validator->getMessages());
                    }

                    break;
            }
        }
        return true;
    }

    /**
     * Return is AJAX Request
     *
     * @return boolean
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsAjaxRequest()
    {
        return $this->_isAjax;
    }

    /**
     * Return Original Attribute value from Request
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return mixed
     */
    protected function _getRequestValue(\Magento\Framework\App\RequestInterface $request)
    {
        $attrCode = $this->getAttribute()->getAttributeCode();
        if ($this->_requestScope) {
            if (strpos($this->_requestScope, '/') !== false) {
                $params = $request->getParams();
                $parts = explode('/', $this->_requestScope);
                foreach ($parts as $part) {
                    if (isset($params[$part])) {
                        $params = $params[$part];
                    } else {
                        $params = [];
                    }
                }
            } else {
                $params = $request->getParam($this->_requestScope);
            }

            if (isset($params[$attrCode])) {
                $value = $params[$attrCode];
            } else {
                $value = false;
            }

            if (!$this->_requestScopeOnly && $value === false) {
                $value = $request->getParam($attrCode, false);
            }
        } else {
            $value = $request->getParam($attrCode, false);
        }
        return $value;
    }

    /**
     * Extract data from request and return value
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return array|string
     */
    abstract public function extractValue(\Magento\Framework\App\RequestInterface $request);

    /**
     * Validate data
     *
     * @param array|string|null $value
     * @return array|bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    abstract public function validateValue($value);

    /**
     * Export attribute value
     *
     * @param array|string $value
     * @return array|string|bool
     */
    abstract public function compactValue($value);

    /**
     * Restore attribute value from SESSION
     *
     * @param array|string $value
     * @return array|string|bool
     */
    abstract public function restoreValue($value);

    /**
     * Return formatted attribute value from entity model
     *
     * @param string $format
     * @return string|array
     */
    abstract public function outputValue(
        $format = \Magento\Customer\Model\Metadata\ElementFactory::OUTPUT_FORMAT_TEXT
    );
}
