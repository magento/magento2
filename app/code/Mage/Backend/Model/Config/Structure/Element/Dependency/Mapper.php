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

class Mage_Backend_Model_Config_Structure_Element_Dependency_Mapper
{
    /**
     * Field locator model
     *
     * @var Mage_Backend_Model_Config_Structure_SearchInterface
     */
    protected $_fieldLocator;

    /**
     * Dependency Field model
     *
     * @var Mage_Backend_Model_Config_Structure_Element_Dependency_FieldFactory
     */
    protected $_fieldFactory;

    /**
     * Application object
     *
     * @var Mage_Core_Model_App
     */
    protected $_application;

    /**
     * @param Mage_Core_Model_App $application
     * @param Mage_Backend_Model_Config_Structure_SearchInterface $fieldLocator
     * @param Mage_Backend_Model_Config_Structure_Element_Dependency_FieldFactory $fieldFactory
     */
    public function __construct(
        Mage_Core_Model_App $application,
        Mage_Backend_Model_Config_Structure_SearchInterface $fieldLocator,
        Mage_Backend_Model_Config_Structure_Element_Dependency_FieldFactory $fieldFactory
    ) {

        $this->_fieldLocator = $fieldLocator;
        $this->_application = $application;
        $this->_fieldFactory = $fieldFactory;
    }

    /**
     * Retrieve field dependencies
     *
     * @param array $dependencies
     * @param string $storeCode
     * @param string $fieldPrefix
     * @return array
     */
    public function getDependencies($dependencies, $storeCode, $fieldPrefix = '')
    {
        $output = array();

        foreach ($dependencies as $depend) {
            $field = $this->_fieldFactory->create(array('fieldData' => $depend, 'fieldPrefix' => $fieldPrefix));
            $shouldAddDependency = true;
            /** @var Mage_Backend_Model_Config_Structure_Element_Field $dependentField  */
            $dependentField = $this->_fieldLocator->getElement($depend['id']);
            /*
            * If dependent field can't be shown in current scope and real dependent config value
            * is not equal to preferred one, then hide dependence fields by adding dependence
            * based on not shown field (not rendered field)
            */
            if (false == $dependentField->isVisible()) {
                $valueInStore = $this->_application
                    ->getStore($storeCode)
                    ->getConfig($dependentField->getPath($fieldPrefix));
                $shouldAddDependency = !$field->isValueSatisfy($valueInStore);
            }
            if ($shouldAddDependency) {
                $output[$field->getId()] = $field;
            }
        }
        return $output;
    }
}
