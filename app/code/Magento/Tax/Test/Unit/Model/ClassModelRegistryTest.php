<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for TaxRuleRegistry
 */
class ClassModelRegistryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Tax\Model\ClassModelRegistry
     */
    private $taxRuleRegistry;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Tax\Model\ClassModelFactory
     */
    private $classModelFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Tax\Model\ClassModel
     */
    private $classModelMock;

    const CLASS_MODEL = 1;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->classModelFactoryMock = $this->getMockBuilder(\Magento\Tax\Model\ClassModelFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->taxRuleRegistry = $objectManager->getObject(
            \Magento\Tax\Model\ClassModelRegistry::class,
            ['taxClassModelFactory' => $this->classModelFactoryMock]
        );
        $this->classModelMock = $this->getMockBuilder(\Magento\Tax\Model\ClassModel::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->classModelFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->classModelMock));
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testUpdateTaxClassNotExistingEntity()
    {
        $taxClassId = 1;

        $this->classModelMock
            ->expects($this->once())
            ->method('getId')
            ->will($this->returnValue(null));

        $this->classModelMock->expects($this->once())
            ->method('load')
            ->with($taxClassId)
            ->will($this->returnValue($this->classModelMock));

        $this->taxRuleRegistry->retrieve($taxClassId);
    }

    public function testGetTaxClass()
    {
        $taxClassId = 1;

        $this->classModelMock
            ->expects($this->exactly(2))
            ->method('getId')
            ->will($this->returnValue($taxClassId));

        $this->classModelMock->expects($this->once())
            ->method('load')
            ->with($taxClassId)
            ->will($this->returnValue($this->classModelMock));

        $this->assertEquals($this->classModelMock, $this->taxRuleRegistry->retrieve($taxClassId));
    }
}
