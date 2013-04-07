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
 * @copyright  Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_SomeModule_Model_Test
{
    public function __construct()
    {
        new Mage_SomeModule_Model_Element_Proxy();
        //Mage::getModel('Mage_SomeModule_Model_Comment_Element_Proxy', array('factory' => $factory));
    }

    /**
     * @param Mage_SomeModule_ModelFactory $factory
     * @param array $data
     */
    public function test(Mage_SomeModule_ModelFactory $factory, array $data = array())
    {
        /**
         * Mage::getModel('Mage_SomeModule_Model_Comment_BlockFactory', array('factory' => $factory));
         */
        Mage::getModel('Mage_SomeModule_Model_BlockFactory', array('factory' => $factory, 'data' => $data));
    }
}