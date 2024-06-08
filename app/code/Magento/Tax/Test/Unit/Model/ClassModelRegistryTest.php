<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Tax\Test\Unit\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Tax\Model\ClassModel;
use Magento\Tax\Model\ClassModelFactory;
use Magento\Tax\Model\ClassModelRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for TaxRuleRegistry
 */
class ClassModelRegistryTest extends TestCase
{
    /**
     * @var ClassModelRegistry
     */
    private $taxRuleRegistry;

    /**
     * @var MockObject|ClassModelFactory
     */
    private $classModelFactoryMock;

    /**
     * @var MockObject|ClassModel
     */
    private $classModelMock;

    const CLASS_MODEL = 1;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->classModelFactoryMock = $this->getMockBuilder(ClassModelFactory::class)
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->taxRuleRegistry = $objectManager->getObject(
            ClassModelRegistry::class,
            ['taxClassModelFactory' => $this->classModelFactoryMock]
        );
        $this->classModelMock = $this->getMockBuilder(ClassModel::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->classModelFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->classModelMock);
    }

    public function testUpdateTaxClassNotExistingEntity()
    {
        $this->expectException(NoSuchEntityException::class);
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
