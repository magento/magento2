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
 * @category    Mage
 * @package     Mage_Backend
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

abstract class Mage_Backend_Model_Config_Structure_ElementAbstract
    implements Mage_Backend_Model_Config_Structure_ElementInterface
{
    /**
     * Element data
     *
     * @var array
     */
    protected $_data = array();

    /**
     * Current configuration scope
     *
     * @var string
     */
    protected $_scope;

    /**
     * Helper factory
     *
     * @var Mage_Core_Model_Factory_Helper
     */
    protected $_helperFactory;

    /**
     * Application object
     *
     * @var Mage_Core_Model_App
     */
    protected $_application;

    /**
     * @param Mage_Core_Model_Factory_Helper $helperFactory
     * @param Mage_Core_Model_App $application
     */
    public function __construct(
        Mage_Core_Model_Factory_Helper $helperFactory,
        Mage_Core_Model_App $application
    ) {
        $this->_helperFactory = $helperFactory;
        $this->_application = $application;
    }

    /**
     * Retrieve element translation module
     *
     * @return string
     */
    protected function _getTranslationModule()
    {
        return (isset($this->_data['module']) ? $this->_data['module'] : 'Mage_Backend') . '_Helper_Data';
    }

    /**
     * Translate element attribute
     *
     * @param string $code
     * @return string
     */
    protected function _getTranslatedAttribute($code)
    {
        if (false == array_key_exists($code, $this->_data)) {
            return '';
        }
        return $this->_helperFactory->get($this->_getTranslationModule())->__($this->_data[$code]);
    }

    /**
     * Set element data
     *
     * @param array $data
     * @param string $scope
     */
    public function setData(array $data, $scope)
    {
        $this->_data = $data;
        $this->_scope = $scope;
    }

    /**
     * Retrieve flyweight data
     *
     * @return array
     */
    public function getData()
    {
        return $this->_data;
    }

    /**
     * Retrieve element id
     *
     * @return string
     */
    public function getId()
    {
        return isset($this->_data['id']) ? $this->_data['id'] : '';
    }

    /**
     * Retrieve element label
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->_getTranslatedAttribute('label');
    }

    /**
     * Retrieve element label
     *
     * @return string
     */
    public function getComment()
    {
        return $this->_getTranslatedAttribute('comment');
    }

    /**
     * Retrieve frontend model class name
     *
     * @return string
     */
    public function getFrontendModel()
    {
        return isset($this->_data['frontend_model']) ? $this->_data['frontend_model'] : '';
    }

    /**
     * Retrieve arbitrary element attribute
     *
     * @param string $key
     * @return mixed
     */
    public function getAttribute($key)
    {
        return array_key_exists($key, $this->_data) ? $this->_data[$key] : null;
    }

    /**
     * Check whether element should be displayed
     *
     * @return bool
     */
    public function isVisible()
    {
        $showInScope = array(
            Mage_Backend_Model_Config_ScopeDefiner::SCOPE_STORE => $this->_hasVisibilityValue('showInStore'),
            Mage_Backend_Model_Config_ScopeDefiner::SCOPE_WEBSITE => $this->_hasVisibilityValue('showInWebsite'),
            Mage_Backend_Model_Config_ScopeDefiner::SCOPE_DEFAULT => $this->_hasVisibilityValue('showInDefault'),
        );

        if ($this->_application->isSingleStoreMode()) {
            $result = !$this->_hasVisibilityValue('hide_in_single_store_mode')
                && array_sum($showInScope);
            return $result;
        }

        return !empty($showInScope[$this->_scope]);
    }

    /**
     * Retrieve value of visibility flag
     *
     * @param string $key
     * @return bool
     */
    protected function _hasVisibilityValue($key)
    {
        return isset($this->_data[$key]) && $this->_data[$key];
    }

    /**
     * Retrieve css class of a tab
     *
     * @return string
     */
    public function getClass()
    {
        return isset($this->_data['class']) ? $this->_data['class'] : '';
    }

    /**
     * Retrieve config path for given id
     *
     * @param string $fieldId
     * @param string $fieldPrefix
     * @return string
     */
    protected function _getPath($fieldId, $fieldPrefix = '')
    {
        $path = isset($this->_data['path']) ? $this->_data['path'] : '';
        return $path . '/' . $fieldPrefix . $fieldId;
    }

    /**
     * Retrieve element config path
     *
     * @param string $fieldPrefix
     * @return string
     */
    public function getPath($fieldPrefix = '')
    {
        return $this->_getPath($this->getId(), $fieldPrefix);
    }
}
