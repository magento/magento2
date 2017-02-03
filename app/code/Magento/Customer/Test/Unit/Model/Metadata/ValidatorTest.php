<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Model\Metadata;

use Magento\Customer\Model\Data\AttributeMetadata;
use Magento\Customer\Model\Metadata\Validator;

class ValidatorTest extends \PHPUnit_Framework_TestCase
{
    /** @var Validator */
    protected $validator;

    /** @var string */
    protected $entityType;

    /** @var \Magento\Customer\Model\Metadata\ElementFactory | \PHPUnit_Framework_MockObject_MockObject */
    protected $attrDataFactoryMock;

    public function setUp()
    {
        $this->attrDataFactoryMock = $this->getMockBuilder(
            '\Magento\Customer\Model\Metadata\ElementFactory'
        )->disableOriginalConstructor()->getMock();

        $this->validator = new \Magento\Customer\Model\Metadata\Validator($this->attrDataFactoryMock);
    }

    public function testValidateDataWithNoDataModel()
    {
        $attribute = $this->getMockBuilder(
            '\Magento\Customer\Api\Data\AttributeMetadataInterface'
        )->disableOriginalConstructor()->getMock();
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
            '\Magento\Customer\Api\Data\AttributeMetadataInterface'
        )->disableOriginalConstructor()->getMock();
        $this->attrDataFactoryMock->expects($this->never())->method('create');
        $this->validator->setAttributes([$attribute]);
        $this->validator->setEntityType('ENTITY_TYPE');
        $this->validator->setData(['something']);
        $this->assertTrue($this->validator->isValid(['entity']));
        $this->validator->setData([]);
        $this->assertTrue($this->validator->isValid(new \Magento\Framework\DataObject([])));
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
        $this->assertEquals($isValid, $this->validator->isValid(new \Magento\Framework\DataObject($data)));
    }

    public function trueFalseDataProvider()
    {
        return [[true], [false]];
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject | AttributeMetadata
     */
    protected function getMockAttribute()
    {
        $attribute = $this->getMockBuilder(
            '\Magento\Customer\Model\Data\AttributeMetadata'
        )->disableOriginalConstructor()->setMethods(
            ['__wakeup', 'getAttributeCode', 'getDataModel']
        )->getMock();
        $attribute->expects($this->any())->method('getAttributeCode')->will($this->returnValue('ATTR_CODE'));
        $attribute->expects($this->any())->method('getDataModel')->will($this->returnValue('DATA_MODEL'));
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
            '\Magento\Customer\Model\Metadata\Form\Text'
        )->disableOriginalConstructor()->getMock();
        $dataModel->expects($this->any())->method('validateValue')->will($this->returnValue($isValid));
        $this->attrDataFactoryMock->expects(
            $this->any()
        )->method(
            'create'
        )->with(
            $this->equalTo($attribute),
            $this->equalTo(null),
            $this->equalTo('ENTITY_TYPE')
        )->will(
            $this->returnValue($dataModel)
        );
    }
}
