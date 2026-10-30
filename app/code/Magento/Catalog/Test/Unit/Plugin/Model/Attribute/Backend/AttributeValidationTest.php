<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Plugin\Model\Attribute\Backend;

use PHPUnit\Framework\Attributes\DataProvider;
use Magento\Catalog\Plugin\Model\Attribute\Backend\AttributeValidation;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AttributeValidationTest extends TestCase
{
    use MockCreationTrait;
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

        $this->attributeMock = $this->createPartialMockWithReflection(
            AbstractAttribute::class,
            ['setAttributeCode', 'getAttributeCode']
        );
        $attributeCode = '';
        $this->attributeMock->method('setAttributeCode')->willReturnCallback(function ($value) use (&$attributeCode) {
            $attributeCode = $value;
        });
        $this->attributeMock->method('getAttributeCode')->willReturnCallback(function () use (&$attributeCode) {
            return $attributeCode;
        });
        
        $this->subjectMock = $this->createPartialMockWithReflection(
            AbstractBackend::class,
            ['setAttribute', 'getAttribute']
        );
        $this->subjectMock->method('setAttribute')->willReturnSelf();
        $this->subjectMock->method('getAttribute')->willReturn($this->attributeMock);

        $this->storeMock = $this->createPartialMockWithReflection(
            Store::class,
            ['setId', 'getId']
        );
        $storeId = null;
        $this->storeMock->method('setId')->willReturnCallback(function ($value) use (&$storeId) {
            $storeId = $value;
        });
        $this->storeMock->method('getId')->willReturnCallback(function () use (&$storeId) {
            return $storeId;
        });
        
        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);
        $this->storeManagerMock->method('getStore')->willReturn($this->storeMock);

        $this->entityMock = $this->createMock(DataObject::class);

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
     */
    #[DataProvider('aroundValidateDataProvider')]
    public function testAroundValidate(bool $shouldProceedRun, bool $defaultStoreUsed, $storeId): void
    {
        $this->isProceedMockCalled = false;
        $attributeCode = 'code';

        $this->storeMock->setId($storeId);

        if ($defaultStoreUsed) {
            $this->attributeMock->setAttributeCode($attributeCode);
            $this->entityMock->method('getData')->willReturnCallback(function ($arg1) use ($attributeCode) {
                if (empty($arg1)) {
                    return [$attributeCode => null];
                } elseif ($arg1 == $attributeCode) {
                    return null;
                }
                return null;
            });
        }

        $this->attributeValidation->aroundValidate($this->subjectMock, $this->proceedMock, $this->entityMock);
        $this->assertSame($shouldProceedRun, $this->isProceedMockCalled);
    }

    /**
     * Data provider for testAroundValidate.
     *
     * @return array
     */
    public static function aroundValidateDataProvider(): array
    {
        return [
            [true, false, '0'],
            [true, false, 0],
            [true, false, null],
            [false, true, 1]
        ];
    }
}
