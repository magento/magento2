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


class Mage_Backend_Model_Config_Source_Email_Identity implements Mage_Core_Model_Option_ArrayInterface
{
    protected $_options = null;
    public function toOptionArray()
    {
        if (is_null($this->_options)) {
            $this->_options = array();
            $config = Mage::getSingleton('Mage_Backend_Model_Config_Structure_Reader')
                ->getConfiguration()
                ->getSection('trans_email');

            $groups = $config['groups'];
            foreach ($groups as $field) {
                $nodeName   = $field['id'];
                $label      = (string) isset($field['label']) ? $field['label'] : '';
                $sortOrder  = (int) isset($field['sortOrder']) ? $field['sortOrder'] : null;
                $this->_options[$sortOrder] = array(
                    'value' => preg_replace('#^ident_(.*)$#', '$1', $nodeName),
                    'label' => Mage::helper('Mage_Backend_Helper_Data')->__($label)
                );
            }
            ksort($this->_options);
        }
        return $this->_options;
    }
}
