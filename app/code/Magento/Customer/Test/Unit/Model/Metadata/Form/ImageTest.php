<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Model\Metadata\Form;

use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Model\FileProcessor;

class ImageTest extends AbstractFormTestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Url\EncoderInterface
     */
    protected $urlEncode;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\MediaStorage\Model\File\Validator\NotProtectedExtension
     */
    protected $fileValidatorMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Filesystem
     */
    protected $fileSystemMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\File\UploaderFactory
     */
    protected $uploaderFactoryMock;

    protected function setUp()
    {
        parent::setUp();

        $this->urlEncode = $this->getMockBuilder('Magento\Framework\Url\EncoderInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->fileValidatorMock = $this->getMockBuilder(
            'Magento\MediaStorage\Model\File\Validator\NotProtectedExtension'
        )
            ->disableOriginalConstructor()
            ->getMock();

        $this->fileSystemMock = $this->getMockBuilder('Magento\Framework\Filesystem')
            ->disableOriginalConstructor()
            ->getMock();

        $this->uploaderFactoryMock = $this->getMockBuilder('Magento\Framework\File\UploaderFactory')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @param array $data
     * @return \Magento\Customer\Model\Metadata\Form\File
     */
    private function initialize(array $data)
    {
        $model = $this->getMock(
            'Magento\Customer\Model\Metadata\Form\Image',
            ['_isUploadedFile'],
            [
                $this->localeMock,
                $this->loggerMock,
                $this->attributeMetadataMock,
                $this->localeResolverMock,
                $data['value'],
                $data['entityTypeCode'],
                $data['isAjax'],
                $this->urlEncode,
                $this->fileValidatorMock,
                $this->fileSystemMock,
                $this->uploaderFactoryMock
            ]
        );

        return $model;
    }

    public function testValidateIsNotValidFile()
    {
        $value = [
            'tmp_name' => 'tmp_file',
            'name' => 'realFileName',
        ];

        $this->attributeMetadataMock->expects($this->once())
            ->method('getStoreLabel')
            ->willReturn('File Input Field Label');

        $model = $this->initialize([
            'value' => $value,
            'isAjax' => false,
            'entityTypeCode' => CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
        ]);

        $this->assertEquals(['"realFileName" is not a valid file.'], $model->validateValue($value));
    }

    public function testValidate()
    {
        $value = [
            'tmp_name' => __DIR__ . '/_files/logo.gif',
            'name' => 'logo.gif',
        ];

        $this->attributeMetadataMock->expects($this->once())
            ->method('getStoreLabel')
            ->willReturn('File Input Field Label');

        $model = $this->initialize([
            'value' => $value,
            'isAjax' => false,
            'entityTypeCode' => CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
        ]);

        $model->expects($this->any())
            ->method('_isUploadedFile')
            ->will($this->returnValue($value['tmp_name']));

        $this->assertTrue($model->validateValue($value));
    }

    public function validateValueToUploadDataProvider()
    {
        $imagePath = __DIR__ . '/_files/logo.gif';
        return [
            [
                ['"realFileName" is not a valid file.'],
                ['tmp_name' => 'tmp_file', 'name' => 'realFileName'],
                ['valid' => false],
            ],
            [true, ['tmp_name' => $imagePath, 'name' => 'logo.gif']]
        ];
    }

    public function testCompactValueUiComponentCustomerNotExists()
    {
        $originValue = 'filename.ext1';

        $value = [
            'file' => 'filename.ext2',
            'name' => 'filename.ext2',
            'type' => 'image',
        ];

        $model = $this->initialize([
            'value' => $originValue,
            'isAjax' => false,
            'entityTypeCode' => CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
        ]);

        $this->assertEquals($originValue, $model->compactValue($value));
    }

    public function testValidateMaxFileSize()
    {
        $value = [
            'tmp_name' => __DIR__ . '/_files/logo.gif',
            'name' => 'logo.gif',
            'size' => 2,
        ];

        $maxFileSize = 1;

        $validationRuleMock = $this->getMockBuilder('Magento\Customer\Api\Data\ValidationRuleInterface')
            ->getMockForAbstractClass();
        $validationRuleMock->expects($this->any())
            ->method('getName')
            ->willReturn('max_file_size');
        $validationRuleMock->expects($this->any())
            ->method('getValue')
            ->willReturn($maxFileSize);

        $this->attributeMetadataMock->expects($this->once())
            ->method('getStoreLabel')
            ->willReturn('File Input Field Label');
        $this->attributeMetadataMock->expects($this->once())
            ->method('getValidationRules')
            ->willReturn([$validationRuleMock]);

        $model = $this->initialize([
            'value' => $value,
            'isAjax' => false,
            'entityTypeCode' => CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
        ]);

        $model->expects($this->any())
            ->method('_isUploadedFile')
            ->will($this->returnValue($value['tmp_name']));

        $this->assertEquals(['"logo.gif" exceeds the allowed file size.'], $model->validateValue($value));
    }

    public function testValidateMaxImageWidth()
    {
        $value = [
            'tmp_name' => __DIR__ . '/_files/logo.gif',
            'name' => 'logo.gif',
        ];

        $maxImageWidth = 1;

        $validationRuleMock = $this->getMockBuilder('Magento\Customer\Api\Data\ValidationRuleInterface')
            ->getMockForAbstractClass();
        $validationRuleMock->expects($this->any())
            ->method('getName')
            ->willReturn('max_image_width');
        $validationRuleMock->expects($this->any())
            ->method('getValue')
            ->willReturn($maxImageWidth);

        $this->attributeMetadataMock->expects($this->once())
            ->method('getStoreLabel')
            ->willReturn('File Input Field Label');
        $this->attributeMetadataMock->expects($this->once())
            ->method('getValidationRules')
            ->willReturn([$validationRuleMock]);

        $model = $this->initialize([
            'value' => $value,
            'isAjax' => false,
            'entityTypeCode' => CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
        ]);

        $model->expects($this->any())
            ->method('_isUploadedFile')
            ->will($this->returnValue($value['tmp_name']));

        $this->assertEquals(['"logo.gif" width exceeds allowed value of 1 px.'], $model->validateValue($value));
    }

    public function testValidateMaxImageHeight()
    {
        $value = [
            'tmp_name' => __DIR__ . '/_files/logo.gif',
            'name' => 'logo.gif',
        ];

        $maxImageHeight = 1;

        $validationRuleMock = $this->getMockBuilder('Magento\Customer\Api\Data\ValidationRuleInterface')
            ->getMockForAbstractClass();
        $validationRuleMock->expects($this->any())
            ->method('getName')
            ->willReturn('max_image_heght');
        $validationRuleMock->expects($this->any())
            ->method('getValue')
            ->willReturn($maxImageHeight);

        $this->attributeMetadataMock->expects($this->once())
            ->method('getStoreLabel')
            ->willReturn('File Input Field Label');
        $this->attributeMetadataMock->expects($this->once())
            ->method('getValidationRules')
            ->willReturn([$validationRuleMock]);

        $model = $this->initialize([
            'value' => $value,
            'isAjax' => false,
            'entityTypeCode' => CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
        ]);

        $model->expects($this->any())
            ->method('_isUploadedFile')
            ->will($this->returnValue($value['tmp_name']));

        $this->assertEquals(['"logo.gif" height exceeds allowed value of 1 px.'], $model->validateValue($value));
    }

    public function testCompactValueNoChanges()
    {
        $originValue = 'filename.ext1';

        $value = [
            'file' => $originValue,
        ];

        $model = $this->initialize([
            'value' => $originValue,
            'isAjax' => false,
            'entityTypeCode' => CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
        ]);

        $this->assertEquals($originValue, $model->compactValue($value));
    }
}
