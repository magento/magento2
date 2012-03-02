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
 * @package     Mage_Captcha
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Data source to fill "Forms" field
 *
 * @category   Mage
 * @package    Mage_Captcha
 * @author     Magento Core Team <core@magentocommerce.com>
 */
abstract class Mage_Captcha_Model_Config_Form_Abstract extends Mage_Core_Model_Config_Data
{
    /**
     * @var string
     */
    protected $_configPath;

    /**
     * Returns options for form multiselect
     *
     * @return array
     */
    public function toOptionArray()
    {
        $optionArray = array();
        /* @var $backendNode Mage_Core_Model_Config_Element */
        $backendNode = Mage::getConfig()->getNode($this->_configPath);
        if ($backendNode) {
            foreach ($backendNode->children() as $formNode) {
                /* @var $formNode Mage_Core_Model_Config_Element */
                if (!empty($formNode->label)) {
                    $optionArray[] = array('label' => (string)$formNode->label, 'value' => $formNode->getName());
                }
            }
        }
        return $optionArray;
    }
}
