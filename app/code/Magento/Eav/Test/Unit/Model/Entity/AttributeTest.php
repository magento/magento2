<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Eav\Test\Unit\Model\Entity;

use Magento\Eav\Model\Entity\Attribute;
use Magento\Eav\Model\Entity\Attribute\FrontendLabel;
use Magento\Eav\Model\Entity\Attribute\FrontendLabelFactory;
use Magento\Eav\Model\ResourceModel\Entity\Attribute as AttributeResource;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Test for EAV Entity attribute model
 */
class AttributeTest extends TestCase
{
    /**
     * Attribute model to be tested
     * @var Attribute|MockObject
     */
    protected $_model;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->_model = $this->createPartialMock(Attribute::class, ['__wakeup']);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->_model = null;
    }

    public function testGetStoreLabelIgnoresPresetLabelForDifferentStore(): void
    {
        $this->_model->setData([
            'store_label' => 'Store A label',
            'store_labels' => [1 => 'Store A label', 2 => 'Store B label'],
            'frontend_label' => 'Default label',
        ]);
        $store = $this->createMock(Store::class);
        $store->expects($this->exactly(5))->method('getId')->willReturn(1);
        $storeManager = $this->createMock(StoreManagerInterface::class);
        $storeManager->expects($this->exactly(5))->method('getStore')->with()->willReturn($store);
        (new ObjectManager($this))->setBackwardCompatibleProperty($this->_model, '_storeManager', $storeManager);

        $this->assertSame('Store B label', $this->_model->getStoreLabel(2));
        $this->assertSame('Store B label', $this->_model->getStoreLabel('2'));
        $this->assertSame('Store A label', $this->_model->getStoreLabel(1));
        $this->assertSame('Default label', $this->_model->getStoreLabel(0));
        $this->assertSame('Default label', $this->_model->getStoreLabel(3));
        $this->assertSame('Store A label', $this->_model->getStoreLabel());
    }

    #[DataProvider('storeIdentifierDataProvider')]
    public function testGetStoreLabelResolvesCurrentStoreAndStoreCode(?string $storeId): void
    {
        $store = $this->createMock(Store::class);
        $store->method('getId')->willReturn(2);
        $storeManager = $this->createMock(StoreManagerInterface::class);
        $storeManager->expects($this->once())->method('getStore')->with($storeId)->willReturn($store);
        (new ObjectManager($this))->setBackwardCompatibleProperty($this->_model, '_storeManager', $storeManager);
        $this->_model->setData('store_labels', [2 => 'Store B label']);

        $this->assertSame('Store B label', $this->_model->getStoreLabel($storeId));
    }

    public static function storeIdentifierDataProvider(): array
    {
        return [
            'current store' => [null],
            'store code' => ['store_b'],
        ];
    }

    public function testGetStoreLabelPreservesPresetLabelWithoutLoadingStoresOrLabels(): void
    {
        $storeManager = $this->createMock(StoreManagerInterface::class);
        $storeManager->expects($this->never())->method('getStore');
        $resource = $this->createMock(AttributeResource::class);
        $resource->expects($this->never())->method('getStoreLabelsByAttributeId');
        $model = $this->_model;
        $objectManager = new ObjectManager($this);
        $objectManager->setBackwardCompatibleProperty($model, '_storeManager', $storeManager);
        $objectManager->setBackwardCompatibleProperty($model, '_resource', $resource);
        $model->setData('store_label', 'Store A label');

        $this->assertSame('Store A label', $model->getStoreLabel());
        $model->setData('store_label', '');
        $this->assertSame('', $model->getStoreLabel());
    }

    #[DataProvider('storeLabelsDataProvider')]
    public function testGetStoreLabelsLoadsOnce(array $labels): void
    {
        $resource = $this->createMock(AttributeResource::class);
        $resource->expects($this->once())->method('getStoreLabelsByAttributeId')->with(42)->willReturn($labels);
        $model = $this->_model;
        $objectManager = new ObjectManager($this);
        $objectManager->setBackwardCompatibleProperty($model, '_resource', $resource);
        $model->setIdFieldName('attribute_id');
        $model->setId(42);

        $this->assertSame($labels, $model->getStoreLabels());
        $this->assertSame($labels, $model->getStoreLabels());
    }

    #[DataProvider('currentStoreIdDataProvider')]
    public function testGetStoreLabelPreservesPresetLabelForCurrentStore(
        int|string $storeId,
        int|string $currentStoreId
    ): void {
        $store = $this->createMock(Store::class);
        $store->expects($this->exactly(2))->method('getId')->willReturn($currentStoreId);
        $storeManager = $this->createMock(StoreManagerInterface::class);
        $storeManager->expects($this->exactly(2))->method('getStore')->with()->willReturn($store);
        $resource = $this->createMock(AttributeResource::class);
        $resource->expects($this->never())->method('getStoreLabelsByAttributeId');
        $objectManager = new ObjectManager($this);
        $objectManager->setBackwardCompatibleProperty($this->_model, '_storeManager', $storeManager);
        $objectManager->setBackwardCompatibleProperty($this->_model, '_resource', $resource);
        $this->_model->setData('store_label', 'Store A label');

        $this->assertSame('Store A label', $this->_model->getStoreLabel($storeId));
        $this->_model->setData('store_label', '');
        $this->assertSame('', $this->_model->getStoreLabel($storeId));
    }

    public static function currentStoreIdDataProvider(): array
    {
        return [
            'integer IDs' => [1, 1],
            'string request' => ['1', 1],
            'string current ID' => [1, '1'],
            'string IDs' => ['1', '1'],
        ];
    }

    public function testGetStoreLabelLoadsDifferentStoreLabelsOnce(): void
    {
        $store = $this->createMock(Store::class);
        $store->expects($this->exactly(2))->method('getId')->willReturn(1);
        $storeManager = $this->createMock(StoreManagerInterface::class);
        $storeManager->expects($this->exactly(2))->method('getStore')->with()->willReturn($store);
        $resource = $this->createMock(AttributeResource::class);
        $resource->expects($this->once())->method('getStoreLabelsByAttributeId')->with(42)
            ->willReturn([1 => 'Store A label', 2 => 'Store B label']);
        $objectManager = new ObjectManager($this);
        $objectManager->setBackwardCompatibleProperty($this->_model, '_storeManager', $storeManager);
        $objectManager->setBackwardCompatibleProperty($this->_model, '_resource', $resource);
        $this->_model->setIdFieldName('attribute_id');
        $this->_model->setId(42);
        $this->_model->setData('store_label', 'Store A label');

        $this->assertSame('Store B label', $this->_model->getStoreLabel(2));
        $this->assertSame('Store B label', $this->_model->getStoreLabel('2'));
    }

    public static function storeLabelsDataProvider(): array
    {
        return [
            'store labels' => [[1 => 'Store A label', 2 => 'Store B label']],
            'no store labels' => [[]],
        ];
    }

    /**
     * @param string $givenFrontendInput
     * @param string $expectedBackendType
     * @return void
     */
    #[DataProvider('dataGetBackendTypeByInput')]
    public function testGetBackendTypeByInput($givenFrontendInput, $expectedBackendType)
    {
        $this->assertEquals($expectedBackendType, $this->_model->getBackendTypeByInput($givenFrontendInput));
    }

    /**
     * @return array
     */
    public static function dataGetBackendTypeByInput()
    {
        return [
            ['unrecognized-frontend-input', null],
            ['text', 'varchar'],
            ['gallery', 'varchar'],
            ['media_image', 'varchar'],
            ['multiselect', 'text'],
            ['image', 'text'],
            ['textarea', 'text'],
            ['date', 'datetime'],
            ['datetime', 'datetime'],
            ['select', 'int'],
            ['boolean', 'int'],
            ['price', 'decimal'],
            ['weight', 'decimal']
        ];
    }

    /**
     * @param string $givenFrontendInput
     * @param string $expectedDefaultValue
     */
    #[DataProvider('dataGetDefaultValueByInput')]
    public function testGetDefaultValueByInput($givenFrontendInput, $expectedDefaultValue)
    {
        $this->assertEquals($expectedDefaultValue, $this->_model->getDefaultValueByInput($givenFrontendInput));
    }

    /**
     * @return array
     */
    public static function dataGetDefaultValueByInput()
    {
        return [
            ['unrecognized-frontend-input', ''],
            ['select', ''],
            ['gallery', ''],
            ['media_image', ''],
            ['multiselect', null],
            ['text', 'default_value_text'],
            ['price', 'default_value_text'],
            ['image', 'default_value_text'],
            ['weight', 'default_value_text'],
            ['textarea', 'default_value_textarea'],
            ['date', 'default_value_date'],
            ['datetime', 'default_value_datetime'],
            ['boolean', 'default_value_yesno']
        ];
    }

    /**
     * @param array|null $sortWeights
     * @param float $expected
     */
    #[DataProvider('getSortWeightDataProvider')]
    public function testGetSortWeight($sortWeights, $expected)
    {
        $setId = 123;
        $this->_model->setAttributeSetInfo([$setId => $sortWeights]);
        $this->assertEquals($expected, $this->_model->getSortWeight($setId));
    }

    /**
     * @return array
     */
    public static function getSortWeightDataProvider()
    {
        return [
            'empty set info' => ['sortWeights' => null, 'expected' => 0],
            'no group sort' => ['sortWeights' => ['sort' => 5], 'expected' => 0.0005],
            'no sort' => ['sortWeights' => ['group_sort' => 7], 'expected' => 7000],
            'group sort and sort' => [
                'sortWeights' => ['group_sort' => 7, 'sort' => 5],
                'expected' => 7000.0005,
            ]
        ];
    }

    /**
     * return void
     */
    public function testGetFrontendLabels()
    {
        $attributeId = 1;
        $storeLabels = ['test_attribute_store1'];
        $frontendLabelFactory = $this->createPartialMock(
            FrontendLabelFactory::class,
            ['create']
        );
        $resource = $this->createPartialMock(
            AttributeResource::class,
            ['getStoreLabelsByAttributeId']
        );
        $objectManager = new ObjectManager($this);
        $objectManager->setBackwardCompatibleProperty($this->_model, '_resource', $resource);
        $objectManager->setBackwardCompatibleProperty(
            $this->_model,
            'frontendLabelFactory',
            $frontendLabelFactory,
            \Magento\Eav\Model\Entity\Attribute\AbstractAttribute::class
        );
        $this->_model->setAttributeId($attributeId);

        $resource->expects($this->once())
            ->method('getStoreLabelsByAttributeId')
            ->with($attributeId)
            ->willReturn($storeLabels);
        $frontendLabel = $this->createPartialMock(
            FrontendLabel::class,
            ['setStoreId', 'setLabel']
        );
        $frontendLabelFactory->expects($this->once())
            ->method('create')
            ->willReturn($frontendLabel);
        $expectedFrontendLabel[] = $frontendLabel;

        $this->assertEquals($expectedFrontendLabel, $this->_model->getFrontendLabels());
    }
}
