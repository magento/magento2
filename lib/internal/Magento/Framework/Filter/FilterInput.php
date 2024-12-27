<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://framework.zend.com/license
 * If you did not receive a copy of the license and are unable to obtain it
 * through the world-wide-web, please send an email to license@zend.com
 * so we can send you a copy immediately.
 *
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (https://www.zend.com/)
 * @license https://framework.zend.com/license New BSD License
 */
declare(strict_types=1);

namespace Magento\Framework\Filter;

use Laminas\Filter\FilterChain;
use Laminas\Filter\FilterInterface;
use Laminas\I18n\Translator\Translator;
use Laminas\I18n\Translator\TranslatorInterface;
use Magento\Framework\Validator\NotEmpty;
use Magento\Framework\Validator\ValidatorChain;

/**
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FilterInput
{
    public const ALLOW_EMPTY = 'allowEmpty';
    public const BREAK_CHAIN = 'breakChainOnFailure';
    public const DEFAULT_VALUE = 'default';
    public const MESSAGES = 'messages';
    public const ESCAPE_FILTER = 'escapeFilter';
    public const FIELDS = 'fields';
    public const FILTER = 'filter';
    public const FILTER_CHAIN = 'filterChain';
    public const MISSING_MESSAGE = 'missingMessage';
    public const INPUT_NAMESPACE = 'inputNamespace';
    public const VALIDATOR_NAMESPACE = 'validatorNamespace';
    public const FILTER_NAMESPACE = 'filterNamespace';
    public const NOT_EMPTY_MESSAGE = 'notEmptyMessage';
    public const PRESENCE = 'presence';
    public const PRESENCE_OPTIONAL = 'optional';
    public const PRESENCE_REQUIRED = 'required';
    public const RULE = 'rule';
    public const RULE_WILDCARD = '*';
    public const VALIDATE = 'validate';
    public const VALIDATOR = 'validator';
    public const VALIDATOR_CHAIN = 'validatorChain';
    public const VALIDATOR_CHAIN_COUNT = 'validatorChainCount';

    /**
     * @var array
     */
    protected $_data = [];

    /**
     * @var array
     */
    protected $_filterRules = [];

    /**
     * @var array
     */
    protected $_validatorRules = [];

    /**
     * @var array
     */
    protected $_validFields = [];

    /**
     * @var array
     */
    protected $_invalidMessages = [];

    /**
     * @var array
     */
    protected $_invalidErrors = [];

    /**
     * @var array
     */
    protected $_missingFields = [];

    /**
     * @var array
     */
    protected $_unknownFields = [];

    /**
     * @var FilterInterface
     */
    protected $_defaultEscapeFilter = null;

    /**
     * @var array
     */
    protected $_loaders = [];

    /**
     * @var array
     */
    protected $_defaults = [
        self::ALLOW_EMPTY => false,
        self::BREAK_CHAIN => false,
        self::ESCAPE_FILTER => 'HtmlEntities',
        self::MISSING_MESSAGE => "Field '%field%' is required by rule '%rule%', but the field is missing",
        self::NOT_EMPTY_MESSAGE => "You must give a non-empty value for field '%field%'",
        self::PRESENCE => self::PRESENCE_OPTIONAL
    ];

    /**
     * @var boolean
     */
    protected $_processed = false;

    /**
     * @var TranslatorInterface
     */
    protected $_translator;

    /**
     * @var Boolean
     */
    protected $_translatorDisabled = false;

    /**
     * @param array $filterRules
     * @param array $validatorRules
     * @param array|null $data
     * @param array|null $options
     */
    public function __construct($filterRules, $validatorRules, array $data = null, array $options = null)
    {
        if ($options) {
            $this->setOptions($options);
        }

        $this->_filterRules = (array) $filterRules;
        $this->_validatorRules = (array) $validatorRules;

        if ($data) {
            $this->setData($data);
        }
    }

    /**
     * Method to get messages.
     *
     * @return array
     */
    public function getMessages()
    {
        $this->_process();
        return array_merge($this->_invalidMessages, $this->_missingFields);
    }

    /**
     * Method to get errors.
     *
     * @return array
     */
    public function getErrors()
    {
        $this->_process();
        return $this->_invalidErrors;
    }

    /**
     * Method to get invalid.
     *
     * @return array
     */
    public function getInvalid()
    {
        $this->_process();
        return $this->_invalidMessages;
    }

    /**
     * Method to get missing.
     *
     * @return array
     */
    public function getMissing()
    {
        $this->_process();
        return $this->_missingFields;
    }

    /**
     * Method to get unknown.
     *
     * @return array
     */
    public function getUnknown()
    {
        $this->_process();
        return $this->_unknownFields;
    }

    /**
     * Method to get escaped.
     *
     * @param string $fieldName
     *
     * @return mixed
     */
    public function getEscaped($fieldName = null)
    {
        $this->_process();
        $this->_getDefaultEscapeFilter();

        if ($fieldName === null) {
            return $this->_escapeRecursive($this->_validFields);
        }
        if (array_key_exists($fieldName, $this->_validFields)) {
            return $this->_escapeRecursive($this->_validFields[$fieldName]);
        }
        return null;
    }

    /**
     * Method to escape recursive.
     *
     * @param array|null $data
     *
     * @return array|null
     */
    protected function _escapeRecursive($data)
    {
        if ($data === null) {
            return $data;
        }

        if (!is_array($data)) {
            return $this->_getDefaultEscapeFilter()->filter($data);
        }
        foreach ($data as &$element) {
            $element = $this->_escapeRecursive($element);
        }

        return $data;
    }

    /**
     * Method to get unescaped.
     *
     * @param string $fieldName
     *
     * @return mixed
     */
    public function getUnescaped($fieldName = null)
    {
        $this->_process();
        if ($fieldName === null) {
            return $this->_validFields;
        }
        if (array_key_exists($fieldName, $this->_validFields)) {
            return $this->_validFields[$fieldName];
        }
        return null;
    }

    /**
     * Method get.
     *
     * @param string $fieldName
     *
     * @return mixed
     */
    public function __get($fieldName)
    {
        return $this->getEscaped($fieldName);
    }

    /**
     * Method to check has invalid.
     *
     * @return boolean
     */
    public function hasInvalid()
    {
        $this->_process();
        return !(empty($this->_invalidMessages));
    }

    /**
     * Method to check has missing.
     *
     * @return boolean
     */
    public function hasMissing()
    {
        $this->_process();
        return !(empty($this->_missingFields));
    }

    /**
     * Method to check has valid.
     *
     * @return boolean
     */
    public function hasValid()
    {
        $this->_process();
        return !(empty($this->_validFields));
    }

    /**
     * Method to check is valid.
     *
     * @param string $fieldName
     *
     * @return boolean
     */
    public function isValid($fieldName = null)
    {
        $this->_process();
        if ($fieldName === null) {
            return !($this->hasMissing() || $this->hasInvalid());
        }
        return array_key_exists($fieldName, $this->_validFields);
    }

    /**
     * Method isset
     *
     * @param string $fieldName
     * @return boolean
     */
    public function __isset($fieldName)
    {
        $this->_process();
        return isset($this->_validFields[$fieldName]);
    }

    /**
     * Method to process.
     *
     * @return FilterInput
     * @throws \Exception
     */
    public function process()
    {
        $this->_process();
        if ($this->hasInvalid()) {
            throw new FilterException("Input has invalid fields");
        }
        if ($this->hasMissing()) {
            throw new FilterException("Input has missing fields");
        }

        return $this;
    }

    /**
     * Method to set data.
     *
     * @param array $data
     *
     * @return FilterInput
     */
    public function setData(array $data)
    {
        $this->_data = $data;
        $this->_validFields = [];
        $this->_invalidMessages = [];
        $this->_invalidErrors = [];
        $this->_missingFields = [];
        $this->_unknownFields = [];
        $this->_processed = false;

        return $this;
    }

    /**
     * Method to set default escape filter.
     *
     * @param mixed $escapeFilter
     *
     * @return FilterInterface
     * @throws \Exception
     */
    public function setDefaultEscapeFilter($escapeFilter)
    {
        if (is_string($escapeFilter) || is_array($escapeFilter)) {
            $escapeFilter = $this->_getFilter($escapeFilter);
        }
        if (!$escapeFilter instanceof FilterInterface) {
            throw new FilterException('Escape filter specified does not implement FilterInterface');
        }
        $this->_defaultEscapeFilter = $escapeFilter;
        return $escapeFilter;
    }

    /**
     * Method to set options.
     *
     * @param array $options
     *
     * @return FilterInput
     * @throws \Exception
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function setOptions(array $options)
    {
        foreach ($options as $option => $value) {
            switch ($option) {
                case self::ESCAPE_FILTER:
                    $this->setDefaultEscapeFilter($value);
                    break;
                case self::INPUT_NAMESPACE:
                case self::VALIDATOR_NAMESPACE:
                case self::FILTER_NAMESPACE:
                case self::ALLOW_EMPTY:
                case self::BREAK_CHAIN:
                case self::MISSING_MESSAGE:
                case self::NOT_EMPTY_MESSAGE:
                case self::PRESENCE:
                    $this->_defaults[$option] = $value;
                    break;
                default:
                    throw new FilterException("Unknown option '$option'");
            }
        }

        return $this;
    }

    /**
     * Set translation object
     *
     * @param  TranslatorInterface $translator
     * @return FilterInput
     */
    public function setTranslator($translator = null)
    {
        $this->_translator = $translator;

        return $this;
    }

    /**
     * Return translation object
     *
     * @return TranslatorInterface
     */
    public function getTranslator()
    {
        if ($this->translatorIsDisabled()) {
            return null;
        }

        if ($this->_translator === null) {
            $this->_translator = new Translator();
        }

        return $this->_translator;
    }

    /**
     * Indicate whether or not translation should be disabled
     *
     * @param bool $flag
     *
     * @return FilterInput
     */
    public function setDisableTranslator($flag)
    {
        $this->_translatorDisabled = (bool) $flag;
        return $this;
    }

    /**
     * Is translation disabled
     *
     * @return bool
     */
    public function translatorIsDisabled()
    {
        return $this->_translatorDisabled;
    }

    /**
     * Method to filter.
     *
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @throws FilterException
     */
    protected function _filter()
    {
        foreach ($this->_filterRules as $ruleName => &$filterRule) {
            if (!is_array($filterRule)) {
                $filterRule = [$filterRule];
            }
            $filterList = [];

            foreach ($filterRule as $key => $value) {
                if (is_int($key)) {
                    $filterList[] = $value;
                }
            }
            $filterRule[self::RULE] = $ruleName;

            if (!isset($filterRule[self::FIELDS])) {
                $filterRule[self::FIELDS] = $ruleName;
            }
            if (!isset($filterRule[self::FILTER_CHAIN])) {
                $filterRule[self::FILTER_CHAIN] = new FilterChain();
                foreach ($filterList as $filter) {
                    if (is_string($filter) || is_array($filter)) {
                        $filter = $this->_getFilter($filter);
                    }
                    $filterRule[self::FILTER_CHAIN]->setOptions(['callbacks' => [['callback' => $filter]]]);
                }
            }
            if ($ruleName == self::RULE_WILDCARD) {
                foreach (array_keys($this->_data) as $field) {
                    // phpcs:ignore Magento2.Performance.ForeachArrayMerge
                    $this->_filterRule(array_merge($filterRule, [self::FIELDS => $field]));
                }
            } else {
                $this->_filterRule($filterRule);
            }
        }
    }

    /**
     * Method to filter rule.
     *
     * @param array $filterRule
     *
     * @return void
     */
    protected function _filterRule(array $filterRule)
    {
        $field = $filterRule[self::FIELDS];
        if (!array_key_exists($field, $this->_data)) {
            return;
        }
        if (is_array($this->_data[$field])) {
            foreach ($this->_data[$field] as $key => $value) {
                $this->_data[$field][$key] = $filterRule[self::FILTER_CHAIN]->filter($value);
            }
        } else {
            $this->_data[$field] =
                $filterRule[self::FILTER_CHAIN]->filter($this->_data[$field]);
        }
    }

    /**
     * Method to get default escape filter.
     *
     * @return FilterInterface
     * @throws \Exception
     */
    protected function _getDefaultEscapeFilter()
    {
        if ($this->_defaultEscapeFilter !== null) {
            return $this->_defaultEscapeFilter;
        }
        return $this->setDefaultEscapeFilter($this->_defaults[self::ESCAPE_FILTER]);
    }

    /**
     * Method to get missing message.
     *
     * @param string $rule
     * @param string $field
     *
     * @return string
     */
    protected function _getMissingMessage($rule, $field)
    {
        $message = $this->_defaults[self::MISSING_MESSAGE];

        if (null !== ($translator = $this->getTranslator())) {
            $message = $translator->translate(self::MISSING_MESSAGE);
        }

        return str_replace(['%rule%', '%field%'], [$rule, $field], $message);
    }

    /**
     * Method to get not empty message
     *
     * @param string $rule
     * @param string $field
     *
     * @return string
     */
    protected function _getNotEmptyMessage($rule, $field)
    {
        $message = $this->_defaults[self::NOT_EMPTY_MESSAGE];

        if (null !== ($translator = $this->getTranslator())) {
            $message = $translator->translate($message);
        }

        return str_replace(['%rule%', '%field%'], [$rule, $field], $message);
    }

    /**
     * Method to process.
     *
     * @return void
     */
    protected function _process()
    {
        if ($this->_processed === false) {
            $this->_filter();
            $this->_validate();
            $this->_processed = true;
        }
    }

    /**
     * Method to validate.
     *
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * phpcs:disable Generic.Metrics.NestingLevel
     */
    protected function _validate()
    {
        if (!$this->_validatorRules) {
            $this->_validFields = $this->_data;
            $this->_data = [];
            return;
        }
        $preserveDefaultNotEmptyMessage = $this->_defaults[self::NOT_EMPTY_MESSAGE];

        foreach ($this->_validatorRules as $ruleName => &$validatorRule) {
            if (!is_array($validatorRule)) {
                $validatorRule = [$validatorRule];
            }
            $validatorList = [];

            foreach ($validatorRule as $key => $value) {
                if (is_int($key)) {
                    $validatorList[$key] = $value;
                }
            }
            $validatorRule[self::RULE] = $ruleName;

            if (!isset($validatorRule[self::FIELDS])) {
                $validatorRule[self::FIELDS] = $ruleName;
            }
            if (!isset($validatorRule[self::BREAK_CHAIN])) {
                $validatorRule[self::BREAK_CHAIN] = $this->_defaults[self::BREAK_CHAIN];
            }
            if (!isset($validatorRule[self::PRESENCE])) {
                $validatorRule[self::PRESENCE] = $this->_defaults[self::PRESENCE];
            }
            if (!isset($validatorRule[self::ALLOW_EMPTY])) {
                $foundNotEmptyValidator = false;

                foreach ($validatorRule as $rule) {
                    if ($rule === NotEmpty::class) {
                        $foundNotEmptyValidator = true;
                        break;
                    }
                    if (is_array($rule)) {
                        $keys = array_keys($rule);
                        $classKey  = array_shift($keys);
                        if (isset($rule[$classKey])) {
                            $ruleClass = $rule[$classKey];
                            if ($ruleClass === NotEmpty::class) {
                                $foundNotEmptyValidator = true;
                                break;
                            }
                        }
                    }
                    if (!is_object($rule)) {
                        continue;
                    }
                    if ($rule instanceof NotEmpty) {
                        $foundNotEmptyValidator = true;
                        break;
                    }
                }
                if (!$foundNotEmptyValidator) {
                    $validatorRule[self::ALLOW_EMPTY] = $this->_defaults[self::ALLOW_EMPTY];
                } else {
                    $validatorRule[self::ALLOW_EMPTY] = false;
                }
            }

            if (!isset($validatorRule[self::MESSAGES])) {
                $validatorRule[self::MESSAGES] = [];
            } elseif (!is_array($validatorRule[self::MESSAGES])) {
                $validatorRule[self::MESSAGES] = [$validatorRule[self::MESSAGES]];
            } elseif (array_intersect_key($validatorList, $validatorRule[self::MESSAGES])) {
                $unifiedMessages = $validatorRule[self::MESSAGES];
                $validatorRule[self::MESSAGES] = [];

                foreach ($validatorList as $key => $validator) {
                    if (array_key_exists($key, $unifiedMessages)) {
                        $validatorRule[self::MESSAGES][$key] = $unifiedMessages[$key];
                    }
                }
            }
            if (!isset($validatorRule[self::VALIDATOR_CHAIN])) {
                $validatorRule[self::VALIDATOR_CHAIN] = new ValidatorChain();

                foreach ($validatorList as $key => $validator) {
                    if (is_string($validator) || is_array($validator)) {
                        $validator = $this->_getValidator($validator);
                    }

                    if (isset($validatorRule[self::MESSAGES][$key])) {
                        $value = $validatorRule[self::MESSAGES][$key];
                        if (is_array($value)) {
                            $validator->setMessages($value);
                        } else {
                            $validator->setMessage($value);
                        }
                        if ($validator instanceof NotEmpty) {
                            if (is_array($value)) {
                                $temp = $value;
                                $this->_defaults[self::NOT_EMPTY_MESSAGE] = array_pop($temp);
                                unset($temp);
                            } else {
                                $this->_defaults[self::NOT_EMPTY_MESSAGE] = $value;
                            }
                        }
                    }

                    $validatorRule[self::VALIDATOR_CHAIN]->addValidator($validator, $validatorRule[self::BREAK_CHAIN]);
                }
                $validatorRule[self::VALIDATOR_CHAIN_COUNT] = count($validatorList);
            }
            if ($ruleName == self::RULE_WILDCARD) {
                foreach (array_keys($this->_data) as $field) {
                    // phpcs:ignore Magento2.Performance.ForeachArrayMerge
                    $this->_validateRule(array_merge($validatorRule, [self::FIELDS => $field]));
                }
            } else {
                $this->_validateRule($validatorRule);
            }
            $this->_defaults[self::NOT_EMPTY_MESSAGE] = $preserveDefaultNotEmptyMessage;
        }
        foreach (array_merge(array_keys($this->_missingFields), array_keys($this->_invalidMessages)) as $rule) {
            foreach ((array) $this->_validatorRules[$rule][self::FIELDS] as $field) {
                unset($this->_data[$field]);
            }
        }
        foreach ($this->_validFields as $field => $value) {
            unset($this->_data[$field]);
        }

        $this->_unknownFields = $this->_data;
    }

    /**
     * Method to validate rule.
     *
     * @param array $validatorRule
     *
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * phpcs:disable Generic.Metrics.NestingLevel
     */
    protected function _validateRule(array $validatorRule)
    {
        $data = [];

        foreach ((array) $validatorRule[self::FIELDS] as $key => $field) {
            if (array_key_exists($field, $this->_data)) {
                $data[$field] = $this->_data[$field];
            } elseif (isset($validatorRule[self::DEFAULT_VALUE])) {
                if (!is_array($validatorRule[self::DEFAULT_VALUE])) {
                    $data[$field] = $validatorRule[self::DEFAULT_VALUE];
                } else {
                    if (isset($validatorRule[self::DEFAULT_VALUE][$key])) {
                        $data[$field] = $validatorRule[self::DEFAULT_VALUE][$key];
                    } elseif ($validatorRule[self::PRESENCE] == self::PRESENCE_REQUIRED) {
                        $this->_missingFields[$validatorRule[self::RULE]][] =
                            $this->_getMissingMessage($validatorRule[self::RULE], $field);
                    }
                }
            } elseif ($validatorRule[self::PRESENCE] == self::PRESENCE_REQUIRED) {
                $this->_missingFields[$validatorRule[self::RULE]][] =
                    $this->_getMissingMessage($validatorRule[self::RULE], $field);
            }
        }
        if (isset($this->_missingFields[$validatorRule[self::RULE]]) &&
            count($this->_missingFields[$validatorRule[self::RULE]]) > 0
        ) {
            return;
        }
        if (count((array) $validatorRule[self::FIELDS]) > 1) {
            if (!$validatorRule[self::ALLOW_EMPTY]) {
                $emptyFieldsFound = false;
                $errorsList = $messages = [];

                foreach ($data as $fieldKey => $field) {
                    if (!($notEmptyValidator = $this->_getNotEmptyValidatorInstance($validatorRule))) {
                        $notEmptyValidator = $this->_getValidator(NotEmpty::class);
                        $notEmptyValidator->setMessage(
                            $this->_getNotEmptyMessage($validatorRule[self::RULE], $fieldKey)
                        );
                    }
                    if (!$notEmptyValidator->isValid($field)) {
                        foreach ($notEmptyValidator->getMessages() as $messageKey => $message) {
                            if (!isset($messages[$messageKey])) {
                                $messages[$messageKey] = $message;
                            } else {
                                $messages[] = $message;
                            }
                        }
                        $errorsList[] = $notEmptyValidator->getErrors();
                        $emptyFieldsFound = true;
                    }
                }
                if ($emptyFieldsFound) {
                    $this->_invalidMessages[$validatorRule[self::RULE]] = $messages;
                    $this->_invalidErrors[$validatorRule[self::RULE]] = array_unique(
                        call_user_func_array('array_merge', $errorsList)
                    );

                    return;
                }
            }

            if (!$validatorRule[self::VALIDATOR_CHAIN]->isValid($data)) {
                $this->_invalidMessages[$validatorRule[self::RULE]] =
                    $validatorRule[self::VALIDATOR_CHAIN]->getMessages();
                $this->_invalidErrors[$validatorRule[self::RULE]] = $validatorRule[self::VALIDATOR_CHAIN]->getErrors();
                return;
            }
        } elseif (count($data) > 0) {
            $fieldNames = array_keys($data);
            $fieldName = reset($fieldNames);
            $field = reset($data);
            $failed = false;

            if (!is_array($field)) {
                $field = [$field];
            }
            if (!($notEmptyValidator = $this->_getNotEmptyValidatorInstance($validatorRule))) {
                $notEmptyValidator = $this->_getValidator(NotEmpty::class);
                $notEmptyValidator->setMessage($this->_getNotEmptyMessage($validatorRule[self::RULE], $fieldName));
            }
            if ($validatorRule[self::ALLOW_EMPTY]) {
                $validatorChain = $validatorRule[self::VALIDATOR_CHAIN];
            } else {
                $validatorChain = new ValidatorChain();
                $validatorChain->addValidator($notEmptyValidator, true);
                $validatorChain->addValidator($validatorRule[self::VALIDATOR_CHAIN]);
            }
            foreach ($field as $value) {
                if ($validatorRule[self::ALLOW_EMPTY] && !$notEmptyValidator->isValid($value)) {
                    continue;
                }

                if (!$validatorChain->isValid($value)) {
                    if (isset($this->_invalidMessages[$validatorRule[self::RULE]])) {
                        $collectedMessages = $this->_invalidMessages[$validatorRule[self::RULE]];
                    } else {
                        $collectedMessages = [];
                    }
                    foreach ($validatorChain->getMessages() as $messageKey => $message) {
                        if (!isset($collectedMessages[$messageKey])) {
                            $collectedMessages[$messageKey] = $message;
                        } else {
                            $collectedMessages[] = $message;
                        }
                    }
                    $this->_invalidMessages[$validatorRule[self::RULE]] = $collectedMessages;

                    if (isset($this->_invalidErrors[$validatorRule[self::RULE]])) {
                        // phpcs:ignore Magento2.Performance.ForeachArrayMerge
                        $this->_invalidErrors[$validatorRule[self::RULE]] = array_merge(
                            $this->_invalidErrors[$validatorRule[self::RULE]],
                            $validatorChain->getErrors()
                        );
                    } else {
                        $this->_invalidErrors[$validatorRule[self::RULE]] = $validatorChain->getErrors();
                    }
                    unset($this->_validFields[$fieldName]);
                    $failed = true;
                    if ($validatorRule[self::BREAK_CHAIN]) {
                        return;
                    }
                }
            }

            if ($failed) {
                return;
            }
        }
        foreach ((array) $validatorRule[self::FIELDS] as $field) {
            if (array_key_exists($field, $data)) {
                $this->_validFields[$field] = $data[$field];
            }
        }
    }

    /**
     * Check a validatorRule for the presence of a NotEmpty validator instance.
     *
     * @param array $validatorRule
     *
     * @return mixed
     */
    protected function _getNotEmptyValidatorInstance($validatorRule)
    {
        foreach ($validatorRule as $value) {
            if (is_object($value) && $value instanceof NotEmpty) {
                return $value;
            }
        }

        return false;
    }

    /**
     * Method to get filter.
     *
     * @param mixed $classBaseName
     *
     * @return object
     * @throws FilterException
     */
    protected function _getFilter($classBaseName)
    {
        return $this->_getFilterOrValidator($classBaseName);
    }

    /**
     * Method to get validator.
     *
     * @param mixed $classBaseName
     *
     * @return object
     * @throws FilterException
     */
    protected function _getValidator($classBaseName)
    {
        return $this->_getFilterOrValidator($classBaseName);
    }

    /**
     * Method to get filter or validator.
     *
     * @param mixed $classBaseName
     *
     * @return object
     * @throws FilterException
     */
    protected function _getFilterOrValidator($classBaseName)
    {
        try {
            $args = [];

            if (is_array($classBaseName)) {
                $args = $classBaseName;
                $classBaseName = array_shift($classBaseName);
            }
            $class = new \ReflectionClass($classBaseName);

            return $class->hasMethod('__construct') ? $class->newInstanceArgs($args) : $class->newInstance();
        } catch (\ReflectionException $exception) {
            throw new FilterException($exception->getMessage());
        }
    }
}
