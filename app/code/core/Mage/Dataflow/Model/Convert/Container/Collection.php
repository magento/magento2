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
 * @package     Mage_Dataflow
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Convert component collection
 *
 * @category   Mage
 * @package    Mage_Dataflow
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Dataflow_Model_Convert_Container_Collection
{

    protected $_items = array();

    protected $_defaultClass = 'Mage_Dataflow_Model_Convert_Container_Generic';

    public function setDefaultClass($className)
    {
        $this->_defaultClass = $className;
        return $this;
    }

    public function addItem($name, Mage_Dataflow_Model_Convert_Container_Interface $item)
    {
        if (is_null($name)) {
            if ($item->getName()) {
                $name = $item->getName();
            } else {
                $name = sizeof($this->_items);
            }
        }

        $this->_items[$name] = $item;

        return $item;
    }

    public function getItem($name)
    {
        if (!isset($this->_items[$name])) {
            $this->addItem($name, new $this->_defaultClass());
        }
        return $this->_items[$name];
    }

    public function hasItem($name)
    {
        return isset($this->_items[$name]);
    }

}
