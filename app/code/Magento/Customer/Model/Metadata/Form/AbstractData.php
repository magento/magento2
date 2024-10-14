<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Model\Metadata\Form;

use Laminas\I18n\Validator\Alpha;
use Laminas\Validator\Date;
use Laminas\Validator\Digits;
use Magento\Framework\Api\ArrayObjectSearch;
use Magento\Framework\Validator\Alnum;
use Magento\Framework\Validator\EmailAddress;
use Magento\Framework\Validator\Hostname;

/**
 * Form Element Abstract Data Model
 *
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
     *
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
        if ($index !== null) {
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
        if (!$filterCode) {
            $filterCode = $this->assignFilter();
        }
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
     * Assign filter code for user defined Date type Attributes
     *
     * @return string|null
     */
    private function assignFilter(): ?string
    {
        $attribute = $this->getAttribute();
        $filterCode = null;
        if ($attribute->getFrontendInput() === 'date' && $attribute->isUserDefined()) {
            $filterCode = 'date';
        }

        return $filterCode;
    }

    /**
     * Get/Set/Reset date filter format
     *
     * @param string|null|false $format
     * @return $this|string
     */
    protected function _dateFilterFormat($format = null)
    {
        if ($format === null) {
            // get format
            if ($this->_dateFilterFormat === null) {
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

        if ($inputValidation !== null) {
            $allowWhiteSpace = false;

            switch ($inputValidation) {
                case 'alphanum-with-spaces':
                    $allowWhiteSpace = true;
                    // Continue to alphanumeric validation
                case 'alphanumeric':
                    $validator = new Alnum($allowWhiteSpace);
                    $validator->setMessage(__('"%1" invalid type entered.', $label), Alnum::INVALID);
                    $validator->setMessage(
                        __('"%1" contains non-alphabetic or non-numeric characters.', $label),
                        Alnum::NOT_ALNUM
                    );
                    $validator->setMessage(__('"%1" is an empty string.', $label), Alnum::STRING_EMPTY);
                    if (!$validator->isValid($value)) {
                        return $validator->getMessages();
                    }
                    break;
                case 'numeric':
                    $validator = new Digits();
                    $validator->setMessage(__('"%1" invalid type entered.', $label), Digits::INVALID);
                    $validator->setMessage(
                        __('"%1" contains non-numeric characters.', $label),
                        Digits::NOT_DIGITS
                    );
                    $validator->setMessage(
                        __('"%1" is an empty string.', $label),
                        Digits::STRING_EMPTY
                    );
                    if (!$validator->isValid($value)) {
                        return $validator->getMessages();
                    }
                    break;
                case 'alpha':
                    $validator = new Alpha(true);
                    $validator->setMessage(__('"%1" invalid type entered.', $label), Alpha::INVALID);
                    $validator->setMessage(
                        __('"%1" contains non-alphabetic characters.', $label),
                        Alpha::NOT_ALPHA
                    );
                    $validator->setMessage(__('"%1" is an empty string.', $label), Alpha::STRING_EMPTY);
                    if (!$validator->isValid($value)) {
                        return $validator->getMessages();
                    }
                    break;
                case 'email':
                    /**
                    __("'%value%' appears to be a DNS hostname but the given punycode notation cannot be decoded")
                    __("Invalid type given. String expected")
                    __("'%value%' appears to be a DNS hostname but contains a dash in an invalid position")
                    __("'%value%' does not match the expected structure for a DNS hostname")
                    __("'%value%' appears to be a DNS hostname but cannot match
                     * against hostname schema for TLD '%tld%'")
                    __("'%value%' does not appear to be a valid local network name")
                    __("'%value%' does not appear to be a valid URI hostname")
                    __("'%value%' appears to be an IP address, but IP addresses are not allowed")
                    __("'%value%' appears to be a local network name but local network names are not allowed")
                    __("'%value%' appears to be a DNS hostname but cannot extract TLD part")
                    __("'%value%' appears to be a DNS hostname but cannot match TLD against known list")
                    */
                    $validator = new EmailAddress();
                    $validator->setOptions(['hostnameValidator' => new Hostname()]);
                    $validator->setMessage(
                        __('"%1" invalid type entered.', $label),
                        EmailAddress::INVALID
                    );
                    $validator->setMessage(
                        __('"%1" is not a valid email address.', $label),
                        EmailAddress::INVALID_FORMAT
                    );
                    $validator->setMessage(
                        __('"%1" is not a valid hostname.', $label),
                        EmailAddress::INVALID_HOSTNAME
                    );
                    $validator->setMessage(
                        __('"%1" is not a valid hostname.', $label),
                        EmailAddress::INVALID_MX_RECORD
                    );
                    $validator->setMessage(
                        __('"%1" is not a valid hostname.', $label),
                        EmailAddress::INVALID_MX_RECORD
                    );
                    $validator->setMessage(
                        __('"%1" is not a valid email address.', $label),
                        EmailAddress::DOT_ATOM
                    );
                    $validator->setMessage(
                        __('"%1" is not a valid email address.', $label),
                        EmailAddress::QUOTED_STRING
                    );
                    $validator->setMessage(
                        __('"%1" is not a valid email address.', $label),
                        EmailAddress::INVALID_LOCAL_PART
                    );
                    $validator->setMessage(
                        __('"%1" uses too many characters.', $label),
                        EmailAddress::LENGTH_EXCEEDED
                    );
                    $validator->setMessage(
                        __("'%value%' looks like an IP address, which is not an acceptable format."),
                        Hostname::IP_ADDRESS_NOT_ALLOWED
                    );
                    $validator->setMessage(
                        __("'%value%' looks like a DNS hostname but contains a dash in an invalid position."),
                        Hostname::INVALID_DASH
                    );
                    $validator->setMessage(
                        __(
                            "'%value%' looks like a DNS hostname but we cannot match it against "
                            . "the hostname schema for TLD '%tld%'."
                        ),
                        Hostname::INVALID_HOSTNAME_SCHEMA
                    );
                    $validator->setMessage(
                        __("'%value%' looks like a DNS hostname but cannot extract TLD part."),
                        Hostname::UNDECIPHERABLE_TLD
                    );
                    $validator->setMessage(
                        __("'%value%' does not look like a valid local network name."),
                        Hostname::INVALID_LOCAL_NAME
                    );
                    $validator->setMessage(
                        __("'%value%' looks like a local network name, which is not an acceptable format."),
                        Hostname::LOCAL_NAME_NOT_ALLOWED
                    );
                    $validator->setMessage(
                        __(
                            "'%value%' appears to be a DNS hostname, but the given punycode notation cannot be decoded."
                        ),
                        Hostname::CANNOT_DECODE_PUNYCODE
                    );
                    if (!$validator->isValid($value)) {
                        return array_unique($validator->getMessages());
                    }
                    break;
                case 'url':
                    // phpcs:ignore Magento2.Functions.DiscouragedFunction
                    $parsedUrl = parse_url($value);
                    if ($parsedUrl === false || empty($parsedUrl['scheme']) || empty($parsedUrl['host'])) {
                        return [__('"%1" is not a valid URL.', $label)];
                    }
                    $validator = new Hostname();
                    if (!$validator->isValid($parsedUrl['host'])) {
                        return [__('"%1" is not a valid URL.', $label)];
                    }
                    break;
                case 'date':
                    $validator = new Date();
                    $validator->setMessage(__('"%1" invalid type entered.', $label), Date::INVALID);
                    $validator->setMessage(__('"%1" is not a valid date.', $label), Date::INVALID_DATE);
                    $validator->setMessage(
                        __('"%1" does not fit the entered date format.', $label),
                        Date::FALSEFORMAT
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
