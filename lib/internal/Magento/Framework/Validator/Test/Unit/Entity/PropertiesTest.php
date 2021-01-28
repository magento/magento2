<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Validator\Test\Unit\Entity;

/**
 * Test for \Magento\Framework\Validator\Entity\Properties
 */
class PropertiesTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\DataObject
     */
    protected $_object;

    protected function setUp(): void
    {
        $this->_object = $this->createPartialMock(
            \Magento\Framework\Model\AbstractModel::class,
            ['hasDataChanges', 'getData', 'getOrigData']
        );
    }

    protected function tearDown(): void
    {
        unset($this->_object);
    }

    /**
     * Testing \Magento\Framework\Validator\Entity\Properties::isValid on invalid argument passed
     *
     */
    public function testIsValidException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Instance of \\Magento\\Framework\\Model\\AbstractModel is expected.');

        $validator = new \Magento\Framework\Validator\Entity\Properties();
        $validator->isValid([]);
    }

    /**
     * Testing \Magento\Framework\Validator\Entity\Properties::isValid with hasDataChanges and invoked setter
     */
    public function testIsValidSuccessWithInvokedSetter()
    {
        $this->_object->expects($this->once())->method('hasDataChanges')->willReturn(true);
        $this->_object->expects($this->once())->method('getData')->with('attr1')->willReturn(1);
        $this->_object->expects($this->once())->method('getOrigData')->with('attr1')->willReturn(1);

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
        $this->_object->expects($this->once())->method('hasDataChanges')->willReturn(false);
        $validator = new \Magento\Framework\Validator\Entity\Properties();
        $validator->setReadOnlyProperties(['attr1']);
        $this->assertTrue($validator->isValid($this->_object));
    }

    /**
     * Testing \Magento\Framework\Validator\Entity\Properties::isValid with changed data and invoked setter
     */
    public function testIsValidFailed()
    {
        $this->_object->expects($this->once())->method('hasDataChanges')->willReturn(true);
        $this->_object->expects($this->once())->method('getData')->with('attr1')->willReturn(1);
        $this->_object->expects($this->once())->method('getOrigData')->with('attr1')->willReturn(2);

        $validator = new \Magento\Framework\Validator\Entity\Properties();
        $validator->setReadOnlyProperties(['attr1']);
        $this->assertFalse($validator->isValid($this->_object));
    }
}
