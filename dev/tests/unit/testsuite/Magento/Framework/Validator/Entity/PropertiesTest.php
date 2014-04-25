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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Framework\Validator\Entity;

/**
 * Test for \Magento\Framework\Validator\Entity\Properties
 */
class PropertiesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Object
     */
    protected $_object;

    protected function setUp()
    {
        $this->_object = $this->getMock('Magento\Framework\Object', array('hasDataChanges', 'getData', 'getOrigData'));
    }

    protected function tearDown()
    {
        unset($this->_object);
    }

    /**
     * Testing \Magento\Framework\Validator\Entity\Properties::isValid on invalid argument passed
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Instance of \Magento\Framework\Object is expected.
     */
    public function testIsValidException()
    {
        $validator = new \Magento\Framework\Validator\Entity\Properties();
        $validator->isValid(array());
    }

    /**
     * Testing \Magento\Framework\Validator\Entity\Properties::isValid with hasDataChanges and invoked setter
     */
    public function testIsValidSuccessWithInvokedSetter()
    {
        $this->_object->expects($this->once())->method('hasDataChanges')->will($this->returnValue(true));
        $this->_object->expects($this->once())->method('getData')->with('attr1')->will($this->returnValue(1));
        $this->_object->expects($this->once())->method('getOrigData')->with('attr1')->will($this->returnValue(1));

        $validator = new \Magento\Framework\Validator\Entity\Properties();
        $validator->setReadOnlyProperties(array('attr1'));
        $this->assertTrue($validator->isValid($this->_object));
    }

    /**
     * Testing \Magento\Framework\Validator\Entity\Properties::isValid without invoked setter
     */
    public function testIsValidSuccessWithoutInvokedSetter()
    {
        $validator = new \Magento\Framework\Validator\Entity\Properties();
        $this->assertTrue($validator->isValid($this->_object));
    }

    /**
     * Testing \Magento\Framework\Validator\Entity\Properties::isValid with unchanged data
     */
    public function testIsValidSuccessWithoutHasDataChanges()
    {
        $this->_object->expects($this->once())->method('hasDataChanges')->will($this->returnValue(false));
        $validator = new \Magento\Framework\Validator\Entity\Properties();
        $validator->setReadOnlyProperties(array('attr1'));
        $this->assertTrue($validator->isValid($this->_object));
    }

    /**
     * Testing \Magento\Framework\Validator\Entity\Properties::isValid with changed data and invoked setter
     */
    public function testIsValidFailed()
    {
        $this->_object->expects($this->once())->method('hasDataChanges')->will($this->returnValue(true));
        $this->_object->expects($this->once())->method('getData')->with('attr1')->will($this->returnValue(1));
        $this->_object->expects($this->once())->method('getOrigData')->with('attr1')->will($this->returnValue(2));

        $validator = new \Magento\Framework\Validator\Entity\Properties();
        $validator->setReadOnlyProperties(array('attr1'));
        $this->assertFalse($validator->isValid($this->_object));
    }
}
