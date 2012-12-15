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
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Backend_Model_Config_Structure_Element_Group
    extends Mage_Backend_Model_Config_Structure_Element_CompositeAbstract
{
    /**
     * Group clone model factory
     *
     * @var Mage_Backend_Model_Config_Clone_Factory
     */
    protected $_cloneModelFactory;

    /**
     *
     * @var Mage_Backend_Model_Config_Structure_Element_Dependency_Mapper
     */
    protected $_dependencyMapper;

    /**
     * @param Mage_Core_Model_Factory_Helper $helperFactory
     * @param Mage_Core_Model_App $application
     * @param Mage_Backend_Model_Config_Structure_Element_Iterator_Field $childrenIterator
     * @param Mage_Backend_Model_Config_Clone_Factory $cloneModelFactory
     * @param Mage_Backend_Model_Config_Structure_Element_Dependency_Mapper $dependencyMapper
     */
    public function __construct(
        Mage_Core_Model_Factory_Helper $helperFactory,
        Mage_Core_Model_App $application,
        Mage_Backend_Model_Config_Structure_Element_Iterator_Field $childrenIterator,
        Mage_Backend_Model_Config_Clone_Factory $cloneModelFactory,
        Mage_Backend_Model_Config_Structure_Element_Dependency_Mapper $dependencyMapper
    ) {
        parent::__construct($helperFactory, $application, $childrenIterator);
        $this->_cloneModelFactory = $cloneModelFactory;
        $this->_dependencyMapper = $dependencyMapper;
    }

    /**
     * Should group fields be cloned
     *
     * @return bool
     */
    public function shouldCloneFields()
    {
        return (isset($this->_data['clone_fields']) && !empty($this->_data['clone_fields']));
    }

    /**
     * Retrieve clone model
     *
     * @return Mage_Core_Model_Abstract
     */
    public function getCloneModel()
    {
        if (!isset($this->_data['clone_model']) || !$this->_data['clone_model']) {
            Mage::throwException('Config form fieldset clone model required to be able to clone fields');
        }
        return $this->_cloneModelFactory->create($this->_data['clone_model']);
    }

    /**
     * Populate form fieldset with group data
     *
     * @param Varien_Data_Form_Element_Fieldset $fieldset
     */
    public function populateFieldset(Varien_Data_Form_Element_Fieldset $fieldset)
    {
        $originalData = array();
        foreach ($this->_data as $key => $value) {
            if (!is_array($value)) {
                $originalData[$key] = $value;
            }
        }
        $fieldset->setOriginalData($originalData);
    }

    /**
     * Check whether group should be expanded
     *
     * @return bool
     */
    public function isExpanded()
    {
        return (bool) (isset($this->_data['expanded']) ? (int) $this->_data['expanded'] : false);
    }

    /**
     * Retrieve group fieldset css
     *
     * @return string
     */
    public function getFieldsetCss()
    {
        return array_key_exists('fieldset_css', $this->_data) ? $this->_data['fieldset_css'] : '';
    }

    /**
     * Retrieve field dependencies
     *
     * @param $storeCode
     * @return array
     */
    public function getDependencies($storeCode)
    {
        $dependencies = array();
        if (false == isset($this->_data['depends']['fields'])) {
            return $dependencies;
        }

        $dependencies = $this->_dependencyMapper->getDependencies($this->_data['depends']['fields'], $storeCode);
        return $dependencies;
    }
}
