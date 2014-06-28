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
    protected $_dataValidators = array();

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
        $this->_setVersionValidators();
        $this->_setTypeValidators();
        $this->_setTitleValidators();
    }

    /**
     * Set version validators
     *
     * @return $this
     */
    protected function _setVersionValidators()
    {
        $versionValidators = array(
            array(
                'name' => 'not_empty',
                'class' => 'Zend_Validate_NotEmpty',
                'break' => true,
                'options' => array(),
                'message' => __('Field can\'t be empty')
            ),
            array(
                'name' => 'available',
                'class' => 'Zend_Validate_Regex',
                'break' => true,
                'options' => array('pattern' => '/^(\d+\.\d+\.\d+(\-[a-zA-Z0-9]+)?)$|^\*$/'),
                'message' => __('Theme version has not compatible format.')
            )
        );

        $this->addDataValidators('theme_version', $versionValidators);

        return $this;
    }

    /**
     * Set title validators
     *
     * @return $this
     */
    protected function _setTitleValidators()
    {
        $titleValidators = array(
            array(
                'name' => 'not_empty',
                'class' => 'Zend_Validate_NotEmpty',
                'break' => true,
                'options' => array(),
                'message' => __('Field title can\'t be empty')
            )
        );

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
        $typeValidators = array(
            array(
                'name' => 'not_empty',
                'class' => 'Zend_Validate_NotEmpty',
                'break' => true,
                'options' => array(),
                'message' => __('Field can\'t be empty')
            ),
            array(
                'name' => 'available',
                'class' => 'Zend_Validate_InArray',
                'break' => true,
                'options' => array(
                    'haystack' => array(
                        \Magento\Framework\View\Design\ThemeInterface::TYPE_PHYSICAL,
                        \Magento\Framework\View\Design\ThemeInterface::TYPE_VIRTUAL,
                        \Magento\Framework\View\Design\ThemeInterface::TYPE_STAGING
                    )
                ),
                'message' => __('Theme type is invalid')
            )
        );

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
            $this->_dataValidators[$dataKey] = array();
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
            return isset($this->_errorMessages[$dataKey]) ? $this->_errorMessages[$dataKey] : array();
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
     * @param \Magento\Framework\Object $data
     * @return bool
     */
    public function validate(\Magento\Framework\Object $data)
    {
        $this->_errorMessages = array();
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
