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
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Tax\Model\ClassModelFactory
     */
    private $classModelFactoryMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Tax\Model\ClassModel
     */
    private $classModelMock;

    const CLASS_MODEL = 1;

    protected function setUp(): void
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
            ->willReturn($this->classModelMock);
    }

    /**
     */
    public function testUpdateTaxClassNotExistingEntity()
    {
        $this->expectException(\Magento\Framework\Exception\NoSuchEntityException::class);

        $taxClassId = 1;

        $this->classModelMock
            ->expects($this->once())
            ->method('getId')
            ->willReturn(null);

        $this->classModelMock->expects($this->once())
            ->method('load')
            ->with($taxClassId)
            ->willReturn($this->classModelMock);

        $this->taxRuleRegistry->retrieve($taxClassId);
    }

    public function testGetTaxClass()
    {
        $taxClassId = 1;

        $this->classModelMock
            ->expects($this->exactly(2))
            ->method('getId')
            ->willReturn($taxClassId);

        $this->classModelMock->expects($this->once())
            ->method('load')
            ->with($taxClassId)
            ->willReturn($this->classModelMock);

        $this->assertEquals($this->classModelMock, $this->taxRuleRegistry->retrieve($taxClassId));
    }
}
