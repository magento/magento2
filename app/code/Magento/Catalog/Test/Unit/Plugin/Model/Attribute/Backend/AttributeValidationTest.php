<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Plugin\Model\Attribute\Backend;

use Magento\Catalog\Plugin\Model\Attribute\Backend\AttributeValidation;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\DataObject;

class AttributeValidationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var AttributeValidation
     */
    private $attributeValidation;

    /**
     * @var StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManagerMock;

    /**
     * @var StoreInterface|\PHPUnit_Framework_MockObject_MockObject
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
     * @var AbstractBackend|\PHPUnit_Framework_MockObject_MockObject
     */
    private $subjectMock;

    /**
     * @var AbstractAttribute|\PHPUnit_Framework_MockObject_MockObject
     */
    private $attributeMock;

    /**
     * @var DataObject|\PHPUnit_Framework_MockObject_MockObject
     */
    private $entityMock;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->attributeMock = $this->getMockBuilder(AbstractBackend::class)
            ->setMethods(['getAttributeCode'])
            ->getMockForAbstractClass();
        $this->subjectMock = $this->getMockBuilder(AbstractBackend::class)
            ->setMethods(['getAttribute'])
            ->getMockForAbstractClass();
        $this->subjectMock->expects($this->any())
            ->method('getAttribute')
            ->willReturn($this->attributeMock);

        $this->storeMock = $this->getMockBuilder(StoreInterface::class)
            ->setMethods(['getId'])
            ->getMockForAbstractClass();
        $this->storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
            ->setMethods(['getStore'])
            ->getMockForAbstractClass();
        $this->storeManagerMock->expects($this->any())
            ->method('getStore')
            ->willReturn($this->storeMock);

        $this->entityMock = $this->getMockBuilder(DataObject::class)
            ->setMethods(['getData'])
            ->getMock();

        $this->allowedEntityTypes = [$this->entityMock];

        $this->proceedMock = function () {
            $this->isProceedMockCalled = true;
        };

        $this->attributeValidation = $objectManager->getObject(
            AttributeValidation::class,
            [
                'storeManager' => $this->storeManagerMock,
                'allowedEntityTypes' => $this->allowedEntityTypes,
            ]
        );
    }

    /**
     * @param bool $shouldProceedRun
     * @param bool $defaultStoreUsed
     * @param null|int|string $storeId
     * @dataProvider aroundValidateDataProvider
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @return void
     */
    public function testAroundValidate(bool $shouldProceedRun, bool $defaultStoreUsed, $storeId)
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
            $this->entityMock->expects($this->at(0))
                ->method('getData')
                ->willReturn([$attributeCode => null]);
            $this->entityMock->expects($this->at(1))
                ->method('getData')
                ->with($attributeCode)
                ->willReturn(null);
        }

        $this->attributeValidation->aroundValidate($this->subjectMock, $this->proceedMock, $this->entityMock);
        $this->assertSame($shouldProceedRun, $this->isProceedMockCalled);
    }

    /**
     * Data provider for testAroundValidate
     * @return array
     */
    public function aroundValidateDataProvider(): array
    {
        return [
            [true, false, '0'],
            [true, false, 0],
            [true, false, null],
            [false, true, 1],
        ];
    }
}
