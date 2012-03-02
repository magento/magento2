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
 * @package     Mage_Eav
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Entity/Attribute/Model - attribute selection source from configuration
 *
 * this class should be abstract, but kept usual for legacy purposes
 *
 * @category   Mage
 * @package    Mage_Eav
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Eav_Model_Entity_Attribute_Source_Config extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    /**
     * Config Node Path
     *
     * @var Mage_Core_Model_Config_Element
     */
    protected $_configNodePath;

    /**
     * Retrieve all options for the source from configuration
     *
     * @throws Mage_Eav_Exception
     * @return array
     */
    public function getAllOptions()
    {
        if ($this->_options === null) {
            $this->_options = array();
            $rootNode = null;
            if ($this->_configNodePath) {
                $rootNode = Mage::getConfig()->getNode($this->_configNodePath);
            }
            if (!$rootNode) {
                throw Mage::exception('Mage_Eav', Mage::helper('Mage_Eav_Helper_Data')->__('Failed to load node %s from config', $this->_configNodePath));
            }
            $options = $rootNode->children();
            if (empty($options)) {
                throw Mage::exception('Mage_Eav', Mage::helper('Mage_Eav_Helper_Data')->__('No options found in config node %s', $this->_configNodePath));
            }
            foreach ($options as $option) {
                $this->_options[] = array(
                    'value' => (string)$option->value,
                    'label' => Mage::helper('Mage_Eav_Helper_Data')->__((string)$option->label)
                );
            }
        }

        return $this->_options;
    }
}
