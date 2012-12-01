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
 * @package     Mage_Tax
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Mage_Tax_Model_System_Config_Source_Tax_Country extends Mage_Directory_Model_Config_Source_Country
{
    protected $_options;

    public function toOptionArray($noEmpty=false)
    {
        $options = parent::toOptionArray($noEmpty);

        if(!$noEmpty) {
            if ($options) {
                $options[0]['label'] = Mage::helper('Mage_Tax_Helper_Data')->__('None');
            } else {
                $options = array(array('value'=>'', 'label'=>$this->helper('Mage_Tax_Helper_Data')->__('None')));
            }
        }

        return $options;
    }
}
