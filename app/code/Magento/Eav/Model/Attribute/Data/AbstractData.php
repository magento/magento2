<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Eav\Model\Attribute\Data;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException as CoreException;
use Magento\Framework\App\ObjectManager;

/**
 * EAV Attribute Abstract Data Model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
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
     * @return string|true
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
        $validateRules = $this->getAttribute()->getValidateRules();

        if (!empty($validateRules['input_validation'])) {
            switch ($validateRules['input_validation']) {
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
                    $validator = new \Zend_Validate_Date(
                        [
                            'format' => \Magento\Framework\Stdlib\DateTime::DATE_INTERNAL_FORMAT,
                            'locale' => $this->_localeResolver->getLocale(),
                        ]
                    );
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
