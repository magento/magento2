<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Design\Theme;

/**
 * Class Validator
 */
class Validator
{
    /**
     * Validators list by data key
     * array('dataKey' => array('validator_name' => [validators], ...), ...)
     *
     * @var array
     */
    protected $_dataValidators = [];

    /**
     * List of errors after validation process
     * array('dataKey' => 'Error message')
     *
     * @var array
     */
    protected $_errorMessages;

    /**
     * Initialize validators
     */
    public function __construct()
    {
        $this->_setTypeValidators();
        $this->_setTitleValidators();
    }

    /**
     * Set title validators
     *
     * @return $this
     */
    protected function _setTitleValidators()
    {
        $titleValidators = [
            [
                'name' => 'not_empty',
                'class' => 'Zend_Validate_NotEmpty',
                'break' => true,
                'options' => [],
                'message' => (string)new \Magento\Framework\Phrase('Field title can\'t be empty'),
            ],
        ];

        $this->addDataValidators('theme_title', $titleValidators);
        return $this;
    }

    /**
     * Set theme type validators
     *
     * @return $this
     */
    protected function _setTypeValidators()
    {
        $typeValidators = [
            [
                'name' => 'not_empty',
                'class' => 'Zend_Validate_NotEmpty',
                'break' => true,
                'options' => [],
                'message' => (string)new \Magento\Framework\Phrase('Theme type can\'t be empty'),
            ],
            [
                'name' => 'available',
                'class' => 'Zend_Validate_InArray',
                'break' => true,
                'options' => [
                    'haystack' => [
                        \Magento\Framework\View\Design\ThemeInterface::TYPE_PHYSICAL,
                        \Magento\Framework\View\Design\ThemeInterface::TYPE_VIRTUAL,
                        \Magento\Framework\View\Design\ThemeInterface::TYPE_STAGING,
                    ],
                ],
                'message' => (string)new \Magento\Framework\Phrase('Theme type is invalid')
            ],
        ];

        $this->addDataValidators('type', $typeValidators);

        return $this;
    }

    /**
     * Add validators
     *
     * @param string $dataKey
     * @param array $validators
     * @return $this
     */
    public function addDataValidators($dataKey, $validators)
    {
        if (!isset($this->_dataValidators[$dataKey])) {
            $this->_dataValidators[$dataKey] = [];
        }
        foreach ($validators as $validator) {
            $this->_dataValidators[$dataKey][$validator['name']] = $validator;
        }
        return $this;
    }

    /**
     * Return error messages for items
     *
     * @param string|null $dataKey
     * @return array
     */
    public function getErrorMessages($dataKey = null)
    {
        if ($dataKey) {
            return isset($this->_errorMessages[$dataKey]) ? $this->_errorMessages[$dataKey] : [];
        }
        return $this->_errorMessages;
    }

    /**
     * Instantiate class validator
     *
     * @param array &$validators
     * @return $this
     */
    protected function _instantiateValidators(array &$validators)
    {
        foreach ($validators as &$validator) {
            if (is_string($validator['class'])) {
                $validator['class'] = new $validator['class']($validator['options']);
                $validator['class']->setDisableTranslator(true);
            }
        }
        return $this;
    }

    /**
     * Validate one data item
     *
     * @param array $validator
     * @param string $dataKey
     * @param mixed $dataValue
     * @return bool
     */
    protected function _validateDataItem($validator, $dataKey, $dataValue)
    {
        if ($validator['class'] instanceof \Zend_Validate_NotEmpty && !$validator['class']->isValid(
            $dataValue
        ) || !empty($dataValue) && !$validator['class']->isValid(
            $dataValue
        )
        ) {
            $this->_errorMessages[$dataKey][] = $validator['message'];
            if ($validator['break']) {
                return false;
            }
        }
        return true;
    }

    /**
     * Validate all data items
     *
     * @param \Magento\Framework\DataObject $data
     * @return bool
     */
    public function validate(\Magento\Framework\DataObject $data)
    {
        $this->_errorMessages = [];
        foreach ($this->_dataValidators as $dataKey => $validators) {
            if (!isset($data[$dataKey])) {
                continue;
            }

            $this->_instantiateValidators($validators);
            foreach ($validators as $validator) {
                if (!$this->_validateDataItem($validator, $dataKey, $data[$dataKey])) {
                    break;
                }
            }
        }
        return empty($this->_errorMessages);
    }
}
