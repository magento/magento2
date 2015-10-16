<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogImportExport\Test\Unit\Model\Import\Product;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\CatalogImportExport\Model\Import\Product\Validator;

class ValidatorTest extends \PHPUnit_Framework_TestCase
{
    /** @var Validator */
    protected $validator;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var array */
    protected $validators = [];

    /** @var \Magento\CatalogImportExport\Model\Import\Product|\PHPUnit_Framework_MockObject_MockObject */
    protected $context;

    /** @var Validator\Media|\PHPUnit_Framework_MockObject_MockObject */
    protected $validatorOne;

    /** @var Validator\Website|\PHPUnit_Framework_MockObject_MockObject */
    protected $validatorTwo;

    protected function setUp()
    {
        $entityTypeModel = $this->getMock(
            'Magento\CatalogImportExport\Model\Import\Product\Type\Simple',
            ['retrieveAttributeFromCache'],
            [],
            '',
            false
        );
        $entityTypeModel->expects($this->any())->method('retrieveAttributeFromCache')->willReturn([]);
        $this->context = $this->getMock(
            '\Magento\CatalogImportExport\Model\Import\Product',
            ['retrieveProductTypeByName', 'retrieveMessageTemplate'],
            [],
            '',
            false
        );
        $this->context->expects($this->any())->method('retrieveProductTypeByName')->willReturn($entityTypeModel);
        $this->context->expects($this->any())->method('retrieveMessageTemplate')->willReturn('');

        $this->validatorOne = $this->getMock(
            'Magento\CatalogImportExport\Model\Import\Product\Validator\Media',
            [],
            [],
            '',
            false
        );
        $this->validatorTwo = $this->getMock(
            'Magento\CatalogImportExport\Model\Import\Product\Validator\Website',
            [],
            [],
            '',
            false
        );

        $this->validators = [$this->validatorOne, $this->validatorTwo];
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->validator = $this->objectManagerHelper->getObject(
            'Magento\CatalogImportExport\Model\Import\Product\Validator',
            ['validators' => $this->validators]
        );
        $this->validator->init($this->context);
    }

    public function testIsBooleanAttributeValid()
    {
        $this->context->expects($this->any())->method('getBehavior')
            ->willReturn(\Magento\ImportExport\Model\Import::BEHAVIOR_REPLACE);
        $result = $this->validator->isAttributeValid(
            'boolean_attribute',
            [
                'type' => 'boolean',
                'apply_to' => ['simple'],
                'is_required' => false
            ],
            [
                'product_type' => 'simple',
                'boolean_attribute' => 'Yes'
            ]
        );
        $this->assertTrue($result);
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
