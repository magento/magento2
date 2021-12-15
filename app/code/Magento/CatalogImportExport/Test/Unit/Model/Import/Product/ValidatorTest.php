<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogImportExport\Test\Unit\Model\Import\Product;

use Magento\CatalogImportExport\Model\Import\Product;
use Magento\CatalogImportExport\Model\Import\Product\Type\Simple;
use Magento\CatalogImportExport\Model\Import\Product\Validator;
use Magento\CatalogImportExport\Model\Import\Product\Validator\Media;
use Magento\CatalogImportExport\Model\Import\Product\Validator\Website;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\ImportExport\Model\Import;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ValidatorTest extends TestCase
{
    /** @var Validator */
    protected $validator;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var array */
    protected $validators = [];

    /** @var Product|MockObject */
    protected $context;

    /** @var Validator\Media|MockObject */
    protected $validatorOne;

    /** @var Validator\Website|MockObject */
    protected $validatorTwo;

    protected function setUp(): void
    {
        $entityTypeModel = $this->createPartialMock(
            Simple::class,
            ['retrieveAttributeFromCache']
        );
        $entityTypeModel->expects($this->any())->method('retrieveAttributeFromCache')->willReturn([]);
        $this->context = $this->createPartialMock(
            Product::class,
            ['retrieveProductTypeByName', 'retrieveMessageTemplate', 'getBehavior']
        );
        $this->context->expects($this->any())->method('retrieveProductTypeByName')->willReturn($entityTypeModel);
        $this->context->expects($this->any())->method('retrieveMessageTemplate')->willReturn('error message');

        $this->validatorOne = $this->createPartialMock(
            Media::class,
            ['init', 'isValid']
        );
        $this->validatorTwo = $this->createPartialMock(
            Website::class,
            ['init', 'isValid', 'getMessages']
        );

        $this->validators = [$this->validatorOne, $this->validatorTwo];
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->validator = $this->objectManagerHelper->getObject(
            Validator::class,
            [
                'string' => new StringUtils(),
                'validators' => $this->validators
            ]
        );
        $this->validator->init($this->context);
    }

    /**
     * @param string $behavior
     * @param array $attrParams
     * @param array $rowData
     * @param bool $isValid
     * @param string $attrCode
     * @dataProvider attributeValidationProvider
     */
    public function testAttributeValidation($behavior, $attrParams, $rowData, $isValid, $attrCode = 'attribute_code')
    {
        $this->context->expects($this->any())->method('getBehavior')->willReturn($behavior);
        $result = $this->validator->isAttributeValid(
            $attrCode,
            $attrParams,
            $rowData
        );
        $this->assertEquals($isValid, $result);
        if (!$isValid) {
            $this->assertTrue($this->validator->hasMessages());
        }
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function attributeValidationProvider()
    {
        return [
            [
                'any_behavior',
                ['apply_to' => ['expected_product_type']],
                ['product_type' => 'not_expected_product_type'],
                true, //validation skipped in such case, so it means attribute is valid
                'any_attribute_code',
            ],
            [
                'any_behavior',
                [],
                ['product_type' => 'any'],
                false,
                Product::COL_SKU
            ],
            [
                'any_behavior',
                ['type' => 'varchar'],
                ['product_type' => 'any', 'sku' => 'sku_value'],
                true,
                Product::COL_SKU
            ],
            [
                'any_behavior',
                ['is_required' => true, 'type' => 'varchar'],
                ['product_type' => 'any', 'attribute_code' => 'value'],
                true
            ],
            [
                Import::BEHAVIOR_APPEND,
                ['is_required' => true, 'type' => 'varchar'],
                ['product_type' => 'any', 'attribute_code' => ''],
                false
            ],
            [
                Import::BEHAVIOR_APPEND,
                ['is_required' => true, 'type' => 'int'],
                ['product_type' => 'any', 'attribute_code' => 'not-int'],
                false
            ],
            [
                Import::BEHAVIOR_APPEND,
                ['is_required' => true, 'type' => 'int'],
                ['product_type' => 'any', 'attribute_code' => '1'],
                true
            ],
            [
                Import::BEHAVIOR_APPEND,
                ['is_required' => true, 'type' => 'decimal'],
                ['product_type' => 'any', 'price' => ''],
                true,
                'price'
            ],
            [
                Import::BEHAVIOR_APPEND,
                ['is_required' => true, 'type' => 'boolean', 'options' => ['yes' => 0, 'no' => 1]],
                ['product_type' => 'any', 'attribute_code' => 'some-value'],
                false
            ],
            [
                Import::BEHAVIOR_APPEND,
                ['is_required' => true, 'type' => 'boolean', 'options' => ['yes' => 0, 'no' => 1]],
                ['product_type' => 'any', 'attribute_code' => 'Yes'],
                true
            ],
            [
                Import::BEHAVIOR_APPEND,
                ['is_required' => true, 'type' => 'multiselect', 'options' => ['option 1' => 0, 'option 2' => 1]],
                ['product_type' => 'any', 'attribute_code' => 'Option 1|Option 2|Option 3'],
                false
            ],
            [
                Import::BEHAVIOR_APPEND,
                ['is_required' => true, 'type' => 'multiselect', 'options' => ['option 1' => 0, 'option 2' => 1]],
                ['product_type' => 'any', 'attribute_code' => 'Option 1|Option 2'],
                true
            ],
            [
                Import::BEHAVIOR_APPEND,
                ['is_required' => true, 'type' => 'multiselect',
                    'options' => ['option 1' => 0, 'option 2' => 1, 'option 3']],
                ['product_type' => 'any', 'attribute_code' => 'Option 1|Option 2|Option 1'],
                false
            ],
            [
                Import::BEHAVIOR_APPEND,
                ['is_required' => true, 'type' => 'multiselect',
                    'options' => ['option 1' => 0, 'option 2' => 1, 'option 3']],
                ['product_type' => 'any', 'attribute_code' => 'Option 3|Option 3|Option 3|Option 1'],
                false
            ],
            [
                Import::BEHAVIOR_APPEND,
                ['is_required' => true, 'type' => 'multiselect', 'options' => ['option 1' => 0]],
                ['product_type' => 'any', 'attribute_code' => 'Option 1|Option 1|Option 1|Option 1'],
                false
            ],
            [
                Import::BEHAVIOR_APPEND,
                ['is_required' => true, 'type' => 'datetime'],
                ['product_type' => 'any', 'attribute_code' => '1/1/15 12am'],
                true
            ],
            [
                Import::BEHAVIOR_APPEND,
                ['is_required' => true, 'type' => 'datetime'],
                ['product_type' => 'any', 'attribute_code' => '1/1/15 13am'],
                false
            ],
            [
                Import::BEHAVIOR_APPEND,
                ['is_required' => true, 'type' => 'varchar', 'is_unique' => true],
                ['product_type' => 'any', 'unique_attribute' => 'unique-value', Product::COL_SKU => 'sku-0'],
                true,
                'unique_attribute'
            ]
        ];
    }

    public function testIsValidCorrect()
    {
        $value = ['product_type' => 'simple'];
        $this->validatorOne->expects($this->once())->method('isValid')->with($value)->willReturn(true);
        $this->validatorTwo->expects($this->once())->method('isValid')->with($value)->willReturn(true);
        $result = $this->validator->isValid($value);
        $this->assertTrue($result);
    }

    public function testIsValidIncorrect()
    {
        $value = ['product_type' => 'simple'];
        $this->validatorOne->expects($this->once())->method('isValid')->with($value)->willReturn(true);
        $this->validatorTwo->expects($this->once())->method('isValid')->with($value)->willReturn(false);
        $messages = ['errorMessage'];
        $this->validatorTwo->expects($this->once())->method('getMessages')->willReturn($messages);
        $result = $this->validator->isValid($value);
        $this->assertFalse($result);
        $this->assertEquals($messages, $this->validator->getMessages());
    }

    public function testInit()
    {
        $this->validatorOne->expects($this->once())->method('init');
        $this->validatorTwo->expects($this->once())->method('init');
        $this->validator->init(null);
    }
}
