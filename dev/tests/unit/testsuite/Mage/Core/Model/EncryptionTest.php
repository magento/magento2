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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Core_Model_EncryptionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider setHelperGetHashDataProvider
     */
    public function testSetHelperGetHash($input)
    {
        $objectManager = $this->getMock('Magento_ObjectManager_Zend', array('get'), array(), '', false);
        $objectManager->expects($this->once())
            ->method('get')
            ->with($this->stringContains('Mage_Core_Helper_Data'))
            ->will($this->returnValue($this->getMock('Mage_Core_Helper_Data', array(), array(), '', false, false)));

        /**
         * @var Mage_Core_Model_Encryption
         */
        $model = new Mage_Core_Model_Encryption($objectManager);
        $model->setHelper($input);
        $model->getHash('password', 1);
    }

    /**
     * @return array
     */
    public function setHelperGetHashDataProvider()
    {
        return array(
            'string' => array('Mage_Core_Helper_Data'),
            'object' => array($this->getMock('Mage_Core_Helper_Data', array(), array(), '', false, false)),
        );
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testSetHelperException()
    {
        $objectManager = $this->getMock('Magento_ObjectManager_Zend', array(), array(), '', false);
        /**
         * @var Mage_Core_Model_Encryption
         */
        $model = new Mage_Core_Model_Encryption($objectManager);
        /** Mock object is not instance of Mage_Code_Helper_Data and should not pass validation */
        $input = $this->getMock('Mage_Code_Helper_Data', array(), array(), '', false);
        $model->setHelper($input);
    }
}
