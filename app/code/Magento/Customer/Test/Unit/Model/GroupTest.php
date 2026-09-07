<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model;

use Magento\Customer\Model\Group;
use Magento\Customer\Model\GroupManagement;
use Magento\Customer\Model\ResourceModel\Group as GroupResource;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\StoresConfig;
use Magento\Tax\Model\ClassModel;
use Magento\Tax\Model\ClassModelFactory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for Customer Group model
 */
class GroupTest extends TestCase
{
    /**
     * @var Group
     */
    private $model;

    /**
     * @var Context|MockObject
     */
    private $contextMock;

    /**
     * @var Registry|MockObject
     */
    private $registryMock;

    /**
     * @var StoresConfig|MockObject
     */
    private $storesConfigMock;

    /**
     * @var DataObjectProcessor|MockObject
     */
    private $dataObjectProcessorMock;

    /**
     * @var ClassModelFactory|MockObject
     */
    private $classModelFactoryMock;

    /**
     * @var GroupResource|MockObject
     */
    private $resourceMock;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->contextMock = $this->createMock(Context::class);
        $this->registryMock = $this->createMock(Registry::class);
        $this->storesConfigMock = $this->createMock(StoresConfig::class);
        $this->dataObjectProcessorMock = $this->createMock(DataObjectProcessor::class);
        $this->classModelFactoryMock = $this->createMock(ClassModelFactory::class);
        $this->resourceMock = $this->createMock(GroupResource::class);

        // Mock event manager to prevent dispatch errors
        $eventManagerMock = $this->getMockBuilder(\Magento\Framework\Event\ManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $eventManagerMock->expects($this->any())
            ->method('dispatch')
            ->willReturn(true);
        
        $this->contextMock->expects($this->any())
            ->method('getEventDispatcher')
            ->willReturn($eventManagerMock);

        $this->objectManager = new ObjectManager($this);
        $this->model = $this->objectManager->getObject(
            Group::class,
            [
                'context' => $this->contextMock,
                'registry' => $this->registryMock,
                'storesConfig' => $this->storesConfigMock,
                'dataObjectProcessor' => $this->dataObjectProcessorMock,
                'classModelFactory' => $this->classModelFactoryMock,
                'resource' => $this->resourceMock
            ]
        );
    }

    /**
     * Test setCode and getCode methods
     *
     * @return void
     */
    public function testSetCodeAndGetCode(): void
    {
        $code = 'test_group_code';
        $result = $this->model->setCode($code);
        
        $this->assertSame($this->model, $result, 'setCode should return $this for method chaining');
        $this->assertEquals($code, $this->model->getCode(), 'getCode should return the code set by setCode');
    }

    /**
     * Test that _prepareData truncates code to max length with ASCII characters
     *
     * @return void
     */
    public function testPrepareDataTruncatesAsciiCode(): void
    {
        $longCode = str_repeat('a', 50); // 50 characters, exceeds max of 32
        $expectedCode = str_repeat('a', Group::GROUP_CODE_MAX_LENGTH);
        
        $this->model->setCode($longCode);
        $this->model->beforeSave();
        
        $this->assertEquals(
            $expectedCode,
            $this->model->getCode(),
            'Code should be truncated to GROUP_CODE_MAX_LENGTH characters'
        );
        $this->assertEquals(
            Group::GROUP_CODE_MAX_LENGTH,
            strlen($this->model->getCode()),
            'Code length should equal GROUP_CODE_MAX_LENGTH'
        );
    }

    /**
     * Test that _prepareData correctly handles multibyte characters
     *
     * @param string $input
     * @param string $expected
     * @param int $expectedLength
     * @return void
     */
    #[DataProvider('multibyteCharacterProvider')]
    public function testPrepareDataHandlesMultibyteCharacters(
        string $input,
        string $expected,
        int $expectedLength
    ): void {
        $this->model->setCode($input);
        $this->model->beforeSave();
        
        $this->assertEquals(
            $expected,
            $this->model->getCode(),
            'Code should be correctly truncated based on character count, not byte count'
        );
        $this->assertEquals(
            $expectedLength,
            mb_strlen($this->model->getCode()),
            'Character count should match expected length'
        );
    }

    /**
     * Data provider for multibyte character tests
     *
     * @return array
     */
    public static function multibyteCharacterProvider(): array
    {
        return [
            'multibyte_within_limit' => [
                'input' => str_repeat('ö', 31),
                'expected' => str_repeat('ö', 31),
                'expectedLength' => 31
            ],
            'multibyte_at_limit' => [
                'input' => str_repeat('ö', 32),
                'expected' => str_repeat('ö', 32),
                'expectedLength' => 32
            ],
            'multibyte_over_limit' => [
                'input' => str_repeat('ö', 40),
                'expected' => str_repeat('ö', 32),
                'expectedLength' => 32
            ],
            'chinese_over_limit' => [
                'input' => str_repeat('中', 40),
                'expected' => str_repeat('中', 32),
                'expectedLength' => 32
            ],
            'mixed_multibyte' => [
                'input' => str_repeat('aö', 20), // 40 characters
                'expected' => str_repeat('aö', 16), // 32 characters
                'expectedLength' => 32
            ],
            'emoji_over_limit' => [
                'input' => str_repeat('😀', 40),
                'expected' => str_repeat('😀', 32),
                'expectedLength' => 32
            ]
        ];
    }

    /**
     * Test getTaxClassName when tax_class_name is already set
     *
     * @return void
     */
    public function testGetTaxClassNameWhenAlreadySet(): void
    {
        $taxClassName = 'Retail Customer';
        $this->model->setData('tax_class_name', $taxClassName);
        
        // Should not call classModelFactory since name is already set
        $this->classModelFactoryMock->expects($this->never())
            ->method('create');
        
        $result = $this->model->getTaxClassName();
        
        $this->assertEquals($taxClassName, $result);
    }

    /**
     * Test getTaxClassName when it needs to be loaded from tax class
     *
     * @return void
     */
    public function testGetTaxClassNameLoadsFromTaxClass(): void
    {
        $taxClassId = 3;
        $taxClassName = 'Retail Customer';
        
        $this->model->setTaxClassId($taxClassId);
        
        $classModelMock = $this->createMock(ClassModel::class);
        $classModelMock->expects($this->once())
            ->method('load')
            ->with($taxClassId)
            ->willReturnSelf();
        $classModelMock->expects($this->once())
            ->method('getClassName')
            ->willReturn($taxClassName);
        
        $this->classModelFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($classModelMock);
        
        $result = $this->model->getTaxClassName();
        
        $this->assertEquals($taxClassName, $result);
        $this->assertEquals($taxClassName, $this->model->getData('tax_class_name'));
    }

    /**
     * Test usesAsDefault returns true when group is default
     *
     * @return void
     */
    public function testUsesAsDefaultReturnsTrue(): void
    {
        $groupId = 1;
        $this->model->setId($groupId);
        
        $this->storesConfigMock->expects($this->once())
            ->method('getStoresConfigByPath')
            ->with(GroupManagement::XML_PATH_DEFAULT_ID)
            ->willReturn([1, 2, 3]);
        
        $result = $this->model->usesAsDefault();
        
        $this->assertTrue($result);
    }

    /**
     * Test usesAsDefault returns false when group is not default
     *
     * @return void
     */
    public function testUsesAsDefaultReturnsFalse(): void
    {
        $groupId = 5;
        $this->model->setId($groupId);
        
        $this->storesConfigMock->expects($this->once())
            ->method('getStoresConfigByPath')
            ->with(GroupManagement::XML_PATH_DEFAULT_ID)
            ->willReturn([1, 2, 3]);
        
        $result = $this->model->usesAsDefault();
        
        $this->assertFalse($result);
    }

    /**
     * Test beforeSave calls _prepareData
     *
     * @return void
     */
    public function testBeforeSaveCallsPrepareData(): void
    {
        $code = str_repeat('a', 50);
        $this->model->setCode($code);
        
        $this->model->beforeSave();
        
        // Verify that code was truncated by _prepareData
        $this->assertEquals(
            Group::GROUP_CODE_MAX_LENGTH,
            strlen($this->model->getCode())
        );
    }

    /**
     * Test GROUP_CODE_MAX_LENGTH constant value
     *
     * @return void
     */
    public function testGroupCodeMaxLengthConstant(): void
    {
        $this->assertEquals(32, Group::GROUP_CODE_MAX_LENGTH);
    }
}
