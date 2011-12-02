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
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Mage_Adminhtml_Model_System_Config_Source_Date_Short
{
    public function toOptionArray()
    {
        $arr = array();
        $arr[] = array('label'=>'', 'value'=>'');
        $arr[] = array('label'=>strftime('MM/DD/YY (%m/%d/%y)'), 'value'=>'%m/%d/%y');
        $arr[] = array('label'=>strftime('MM/DD/YYYY (%m/%d/%Y)'), 'value'=>'%m/%d/%Y');
        $arr[] = array('label'=>strftime('DD/MM/YY (%d/%m/%y)'), 'value'=>'%d/%m/%y');
        $arr[] = array('label'=>strftime('DD/MM/YYYY (%d/%m/%Y)'), 'value'=>'%d/%m/%Y');
        return $arr;
    }
}
