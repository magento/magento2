<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Plugin\Model\Attribute\Backend;

use Magento\Catalog\Plugin\Model\Attribute\Backend\AttributeValidation;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AttributeValidationTest extends TestCase
{
    /**
     * @var AttributeValidation
     */
    private $attributeValidation;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManagerMock;

    /**
     * @var StoreInterface|MockObject
     */
    private $storeMock;

    /**
     * @var array
     */
    private $allowedEntityTypes;

    /**
     * @var \Callable
     */
    private $proceedMock;

    /**
     * @var bool
     */
    private $isProceedMockCalled = false;

    /**
     * @var AbstractBackend|MockObject
     */
    private $subjectMock;

    /**
     * @var AbstractAttribute|MockObject
     */
    private $attributeMock;

    /**
     * @var DataObject|MockObject
     */
    private $entityMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->attributeMock = $this->getMockBuilder(AbstractBackend::class)
            ->addMethods(['getAttributeCode'])
            ->getMockForAbstractClass();
        $this->subjectMock = $this->getMockBuilder(AbstractBackend::class)
            ->onlyMethods(['getAttribute'])
            ->getMockForAbstractClass();
        $this->subjectMock->expects($this->any())
            ->method('getAttribute')
            ->willReturn($this->attributeMock);

        $this->storeMock = $this->getMockBuilder(StoreInterface::class)
            ->onlyMethods(['getId'])
            ->getMockForAbstractClass();
        $this->storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
            ->onlyMethods(['getStore'])
            ->getMockForAbstractClass();
        $this->storeManagerMock->expects($this->any())
            ->method('getStore')
            ->willReturn($this->storeMock);

        $this->entityMock = $this->getMockBuilder(DataObject::class)
            ->onlyMethods(['getData'])
            ->getMock();

        $this->allowedEntityTypes = [$this->entityMock];

        $this->proceedMock = function () {
            $this->isProceedMockCalled = true;
        };

        $this->attributeValidation = $objectManager->getObject(
            AttributeValidation::class,
            [
                'storeManager' => $this->storeManagerMock,
                'allowedEntityTypes' => $this->allowedEntityTypes
            ]
        );
    }

    /**
     * @param bool $shouldProceedRun
     * @param bool $defaultStoreUsed
     * @param null|int|string $storeId
     *
     * @return void
     * @throws NoSuchEntityException
     * @dataProvider aroundValidateDataProvider
     */
    public function testAroundValidate(bool $shouldProceedRun, bool $defaultStoreUsed, $storeId): void
    {
        $this->isProceedMockCalled = false;
        $attributeCode = 'code';

        $this->storeMock->expects($this->once())
            ->method('getId')
            ->willReturn($storeId);

        if ($defaultStoreUsed) {
            $this->attributeMock->expects($this->once())
                ->method('getAttributeCode')
                ->willReturn($attributeCode);
            $this->entityMock
                ->method('getData')
                ->withConsecutive([], [$attributeCode])
                ->willReturnOnConsecutiveCalls([$attributeCode => null], null);
        }

        $this->attributeValidation->aroundValidate($this->subjectMock, $this->proceedMock, $this->entityMock);
        $this->assertSame($shouldProceedRun, $this->isProceedMockCalled);
    }

    /**
     * Data provider for testAroundValidate.
     *
     * @return array
     */
    public function aroundValidateDataProvider(): array
    {
        return [
            [true, false, '0'],
            [true, false, 0],
            [true, false, null],
            [false, true, 1]
        ];
    }
}
