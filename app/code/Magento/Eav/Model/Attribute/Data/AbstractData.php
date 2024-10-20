<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Eav\Model\Attribute\Data;

use Laminas\I18n\Validator\Alpha;
use Laminas\Validator\Date;
use Laminas\Validator\Digits;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException as CoreException;
use Magento\Framework\Validator\Alnum;
use Magento\Framework\Validator\EmailAddress;
use Magento\Framework\Validator\Hostname;

/**
 * EAV Attribute Abstract Data Model
 *
 * phpcs:disable Magento2.Classes.AbstractApi
 * @api
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
abstract class AbstractData
{
    /**
     * Attribute instance
     *
     * @var \Magento\Eav\Model\Attribute
     */
    protected $_attribute;

    /**
     * Entity instance
     *
     * @var \Magento\Framework\Model\AbstractModel
     */
    protected $_entity;

    /**
     * Request Scope name
     *
     * @var string
     */
    protected $_requestScope;

    /**
     * Scope visibility flag
     *
     * @var bool
     */
    protected $_requestScopeOnly = true;

    /**
     * Is AJAX request flag
     *
     * @var bool
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
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Locale\ResolverInterface $localeResolver
    ) {
        $this->_localeDate = $localeDate;
        $this->_logger = $logger;
        $this->_localeResolver = $localeResolver;
    }

    /**
     * Set attribute instance
     *
     * @param \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute
     * @return $this
     * @codeCoverageIgnore
     */
    public function setAttribute(\Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute)
    {
        $this->_attribute = $attribute;
        return $this;
    }

    /**
     * Return Attribute instance
     *
     * @throws CoreException
     * @return \Magento\Eav\Model\Attribute
     */
    public function getAttribute()
    {
        if (!$this->_attribute) {
            throw new CoreException(__('Attribute object is undefined'));
        }
        return $this->_attribute;
    }

    /**
     * Set Request scope
     *
     * @param string $scope
     * @return $this
     * @codeCoverageIgnore
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
     * @param bool $flag
     * @return $this
     * @codeCoverageIgnore
     */
    public function setRequestScopeOnly($flag)
    {
        $this->_requestScopeOnly = (bool)$flag;
        return $this;
    }

    /**
     * Set entity instance
     *
     * @param \Magento\Framework\Model\AbstractModel $entity
     * @return $this
     * @codeCoverageIgnore
     */
    public function setEntity(\Magento\Framework\Model\AbstractModel $entity)
    {
        $this->_entity = $entity;
        return $this;
    }

    /**
     * Returns entity instance
     *
     * @return \Magento\Framework\Model\AbstractModel
     * @throws CoreException
     */
    public function getEntity()
    {
        if (!$this->_entity) {
            throw new CoreException(__('Entity object is undefined'));
        }
        return $this->_entity;
    }

    /**
     * Set array of full extracted data
     *
     * @param array $data
     * @return $this
     * @codeCoverageIgnore
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
     * @return mixed
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
     * @return string
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

        $validateRules = $this->getAttribute()->getValidateRules();

        if (!empty($validateRules['input_validation'])) {
            $label = $this->getAttribute()->getStoreLabel();
            $allowWhiteSpace = false;
            switch ($validateRules['input_validation']) {
                case 'alphanum-with-spaces':
                    $allowWhiteSpace = true;
                    // Continue to alphanumeric validation
                    // no break
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
                    __("'%value%' appears to be a DNS hostname but cannot match against hostname schema
                     * for TLD '%tld%'")
                    __("'%value%' does not appear to be a valid local network name")
                    __("'%value%' does not appear to be a valid URI hostname")
                    __("'%value%' appears to be an IP address but IP addresses are not allowed")
                    __("'%value%' appears to be a local network name but local network names are not allowed")
                    __("'%value%' appears to be a DNS hostname but cannot extract TLD part")
                    __("'%value%' appears to be a DNS hostname but cannot match TLD against known list")
                    */
                    $validator = new EmailAddress();
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
                            "'%value%' looks like a DNS hostname but we cannot match it against the "
                            . "hostname schema for TLD '%tld%'."
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
                    $validator = new Date(
                        [
                            'format' => \Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT,
                            'locale' => $this->_localeResolver->getLocale(),
                        ]
                    );
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
     * Set is AJAX Request flag
     *
     * @param bool $flag
     * @return $this
     * @codeCoverageIgnore
     */
    public function setIsAjaxRequest($flag = true)
    {
        $this->_isAjax = (bool)$flag;
        return $this;
    }

    /**
     * Return is AJAX Request
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     * @codeCoverageIgnore
     */
    public function getIsAjaxRequest()
    {
        return $this->_isAjax;
    }

    /**
     * Return Original Attribute value from Request
     *
     * @param RequestInterface $request
     * @return mixed
     */
    protected function _getRequestValue(RequestInterface $request)
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
     * @param RequestInterface $request
     * @return array|string|bool
     */
    abstract public function extractValue(RequestInterface $request);

    /**
     * Validate data
     *
     * @param array|string $value
     * @throws CoreException
     * @return bool
     */
    abstract public function validateValue($value);

    /**
     * Export attribute value to entity model
     *
     * @param array|string $value
     * @return $this
     */
    abstract public function compactValue($value);

    /**
     * Restore attribute value from SESSION to entity model
     *
     * @param array|string $value
     * @return $this
     */
    abstract public function restoreValue($value);

    /**
     * Return formatted attribute value from entity model
     *
     * @param string $format
     * @return string|array
     */
    abstract public function outputValue($format = \Magento\Eav\Model\AttributeDataFactory::OUTPUT_FORMAT_TEXT);
}
