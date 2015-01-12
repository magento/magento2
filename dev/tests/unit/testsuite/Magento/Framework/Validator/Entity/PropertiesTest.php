<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
        $this->_object = $this->getMock('Magento\Framework\Object', ['hasDataChanges', 'getData', 'getOrigData']);
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
        $validator->isValid([]);
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
        $validator->setReadOnlyProperties(['attr1']);
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
        $validator->setReadOnlyProperties(['attr1']);
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
        $validator->setReadOnlyProperties(['attr1']);
        $this->assertFalse($validator->isValid($this->_object));
    }
}
