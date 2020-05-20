<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model\Metadata;

use Magento\Customer\Api\Data\AttributeMetadataInterface;
use Magento\Customer\Model\Data\AttributeMetadata;
use Magento\Customer\Model\Metadata\ElementFactory;
use Magento\Customer\Model\Metadata\Form\Text;
use Magento\Customer\Model\Metadata\Validator;
use Magento\Framework\DataObject;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ValidatorTest extends TestCase
{
    /** @var Validator */
    protected $validator;

    /** @var string */
    protected $entityType;

    /** @var ElementFactory|MockObject */
    protected $attrDataFactoryMock;

    protected function setUp(): void
    {
        $this->attrDataFactoryMock = $this->getMockBuilder(
            ElementFactory::class
        )->disableOriginalConstructor()
            ->getMock();

        $this->validator = new Validator($this->attrDataFactoryMock);
    }

    public function testValidateDataWithNoDataModel()
    {
        $attribute = $this->getMockBuilder(
            AttributeMetadataInterface::class
        )->disableOriginalConstructor()
            ->getMock();
        $this->attrDataFactoryMock->expects($this->never())->method('create');
        $this->assertTrue($this->validator->validateData([], [$attribute], 'ENTITY_TYPE'));
    }

    /**
     * @param bool $isValid
     * @dataProvider trueFalseDataProvider
     */
    public function testValidateData($isValid)
    {
        $attribute = $this->getMockAttribute();
        $this->mockDataModel($isValid, $attribute);
        $this->assertEquals($isValid, $this->validator->validateData([], [$attribute], 'ENTITY_TYPE'));
    }

    public function testIsValidWithNoModel()
    {
        $attribute = $this->getMockBuilder(
            AttributeMetadataInterface::class
        )->disableOriginalConstructor()
            ->getMock();
        $this->attrDataFactoryMock->expects($this->never())->method('create');
        $this->validator->setAttributes([$attribute]);
        $this->validator->setEntityType('ENTITY_TYPE');
        $this->validator->setData(['something']);
        $this->assertTrue($this->validator->isValid(['entity']));
        $this->validator->setData([]);
        $this->assertTrue($this->validator->isValid(new DataObject([])));
    }

    /**
     * @param bool $isValid
     * @dataProvider trueFalseDataProvider
     */
    public function testIsValid($isValid)
    {
        $data = ['something'];
        $attribute = $this->getMockAttribute();
        $this->mockDataModel($isValid, $attribute);
        $this->validator->setAttributes([$attribute]);
        $this->validator->setEntityType('ENTITY_TYPE');
        $this->validator->setData($data);
        $this->assertEquals($isValid, $this->validator->isValid(['ENTITY']));
        $this->validator->setData([]);
        $this->assertEquals($isValid, $this->validator->isValid(new DataObject($data)));
    }

    /**
     * @return array
     */
    public function trueFalseDataProvider()
    {
        return [[true], [false]];
    }

    /**
     * @return MockObject|AttributeMetadata
     */
    protected function getMockAttribute()
    {
        $attribute = $this->getMockBuilder(
            AttributeMetadata::class
        )->disableOriginalConstructor()
            ->setMethods(
                ['__wakeup', 'getAttributeCode', 'getDataModel']
            )->getMock();
        $attribute->expects($this->any())->method('getAttributeCode')->willReturn('ATTR_CODE');
        $attribute->expects($this->any())->method('getDataModel')->willReturn('DATA_MODEL');
        return $attribute;
    }

    /**
     * @param bool $isValid
     * @param AttributeMetadata $attribute
     * @return void
     */
    protected function mockDataModel($isValid, AttributeMetadata $attribute)
    {
        $dataModel = $this->getMockBuilder(
            Text::class
        )->disableOriginalConstructor()
            ->getMock();
        $dataModel->expects($this->any())->method('validateValue')->willReturn($isValid);
        $this->attrDataFactoryMock->expects(
            $this->any()
        )->method(
            'create'
        )->with(
            $attribute,
            null,
            'ENTITY_TYPE'
        )->willReturn(
            $dataModel
        );
    }
}
