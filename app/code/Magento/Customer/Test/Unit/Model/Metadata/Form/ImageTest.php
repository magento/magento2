<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Model\Metadata\Form;

use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Model\FileProcessor;
use Magento\Customer\Model\FileProcessorFactory;

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
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\Request\Http
     */
    protected $requestMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\File\UploaderFactory
     */
    protected $uploaderFactoryMock;

    /**
     * @var FileProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $fileProcessorMock;

    /**
     * @var FileProcessorFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $fileProcessorFactory;

    /**
     * @var \Magento\Framework\Api\Data\ImageContentInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $imageContentFactory;

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

        $this->requestMock = $this->getMockBuilder('Magento\Framework\App\Request\Http')
            ->disableOriginalConstructor()
            ->getMock();

        $this->uploaderFactoryMock = $this->getMockBuilder('Magento\Framework\File\UploaderFactory')
            ->disableOriginalConstructor()
            ->getMock();

        $this->fileProcessorMock = $this->getMockBuilder('Magento\Customer\Model\FileProcessor')
            ->disableOriginalConstructor()
            ->getMock();

        $this->fileProcessorFactory = $this->getMockBuilder('Magento\Customer\Model\FileProcessorFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->imageContentFactory = $this->getMockBuilder('Magento\Framework\Api\Data\ImageContentInterfaceFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
    }

    /**
     * @param array $data
     * @return \Magento\Customer\Model\Metadata\Form\File
     */
    private function initialize(array $data)
    {
        $this->fileProcessorFactory->expects($this->any())->method('create')
            ->willReturn($this->fileProcessorMock);
        $model = new \Magento\Customer\Model\Metadata\Form\Image(
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
            $this->uploaderFactoryMock,
            $this->fileProcessorFactory,
            $this->imageContentFactory
        );

        return $model;
    }

    public function testValidateIsNotValidFile()
    {
        $value = [
            'tmp_name' => 'tmp_file',
            'name' => 'realFileName',
        ];

        $this->attributeMetadataMock->expects($this->atLeastOnce())
            ->method('getStoreLabel')
            ->willReturn('File Input Field Label');

        $this->fileProcessorMock->expects($this->atLeastOnce())
            ->method('isExist')
            ->with(FileProcessor::TMP_DIR . '/' . $value['tmp_name'])
            ->willReturn(true);

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

        $this->attributeMetadataMock->expects($this->atLeastOnce())
            ->method('getStoreLabel')
            ->willReturn('File Input Field Label');

        $this->fileProcessorMock->expects($this->atLeastOnce())
            ->method('isExist')
            ->with(FileProcessor::TMP_DIR . '/' . $value['name'])
            ->willReturn(true);

        $model = $this->initialize([
            'value' => $value,
            'isAjax' => false,
            'entityTypeCode' => CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
        ]);

        $this->assertTrue($model->validateValue($value));
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

        $this->attributeMetadataMock->expects($this->atLeastOnce())
            ->method('getStoreLabel')
            ->willReturn('File Input Field Label');
        $this->attributeMetadataMock->expects($this->atLeastOnce())
            ->method('getValidationRules')
            ->willReturn([$validationRuleMock]);
        $this->fileProcessorMock->expects($this->atLeastOnce())
            ->method('isExist')
            ->with(FileProcessor::TMP_DIR . '/' . $value['name'])
            ->willReturn(true);

        $model = $this->initialize([
            'value' => $value,
            'isAjax' => false,
            'entityTypeCode' => CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
        ]);

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

        $this->attributeMetadataMock->expects($this->atLeastOnce())
            ->method('getStoreLabel')
            ->willReturn('File Input Field Label');
        $this->attributeMetadataMock->expects($this->atLeastOnce())
            ->method('getValidationRules')
            ->willReturn([$validationRuleMock]);
        $this->fileProcessorMock->expects($this->atLeastOnce())
            ->method('isExist')
            ->with(FileProcessor::TMP_DIR . '/' . $value['name'])
            ->willReturn(true);

        $model = $this->initialize([
            'value' => $value,
            'isAjax' => false,
            'entityTypeCode' => CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
        ]);

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

        $this->attributeMetadataMock->expects($this->atLeastOnce())
            ->method('getStoreLabel')
            ->willReturn('File Input Field Label');
        $this->attributeMetadataMock->expects($this->atLeastOnce())
            ->method('getValidationRules')
            ->willReturn([$validationRuleMock]);

        $this->fileProcessorMock->expects($this->atLeastOnce())
            ->method('isExist')
            ->with(FileProcessor::TMP_DIR . '/' . $value['name'])
            ->willReturn(true);

        $model = $this->initialize([
            'value' => $value,
            'isAjax' => false,
            'entityTypeCode' => CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
        ]);

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

    public function testCompactValueUiComponentAddress()
    {
        $originValue = 'filename.ext1';

        $value = [
            'file' => 'filename.ext2',
        ];

        $this->fileProcessorMock->expects($this->atLeastOnce())
            ->method('moveTemporaryFile')
            ->with($value['file'])
            ->willReturn(true);

        $model = $this->initialize([
            'value' => $originValue,
            'isAjax' => false,
            'entityTypeCode' => AddressMetadataInterface::ENTITY_TYPE_ADDRESS,
        ]);

        $this->assertTrue($model->compactValue($value));
    }

    public function testCompactValueUiComponentCustomer()
    {
        $originValue = 'filename.ext1';

        $value = [
            'file' => 'filename.ext2',
            'name' => 'filename.ext2',
            'type' => 'image',
        ];

        $base64EncodedData = 'encoded_data';

        $this->fileProcessorMock->expects($this->atLeastOnce())
            ->method('isExist')
            ->with(FileProcessor::TMP_DIR . '/' . $value['file'])
            ->willReturn(true);
        $this->fileProcessorMock->expects($this->atLeastOnce())
            ->method('getBase64EncodedData')
            ->with(FileProcessor::TMP_DIR . '/' . $value['file'])
            ->willReturn($base64EncodedData);
        $this->fileProcessorMock->expects($this->atLeastOnce())
            ->method('removeUploadedFile')
            ->with(FileProcessor::TMP_DIR . '/' . $value['file'])
            ->willReturnSelf();

        $imageContentMock = $this->getMockBuilder('Magento\Framework\Api\Data\ImageContentInterface')
            ->getMockForAbstractClass();
        $imageContentMock->expects($this->atLeastOnce())
            ->method('setName')
            ->with($value['name'])
            ->willReturnSelf();
        $imageContentMock->expects($this->atLeastOnce())
            ->method('setBase64EncodedData')
            ->with($base64EncodedData)
            ->willReturnSelf();
        $imageContentMock->expects($this->atLeastOnce())
            ->method('setType')
            ->with($value['type'])
            ->willReturnSelf();

        $this->imageContentFactory->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($imageContentMock);

        $model = $this->initialize([
            'value' => $originValue,
            'isAjax' => false,
            'entityTypeCode' => CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
        ]);

        $this->assertEquals($imageContentMock, $model->compactValue($value));
    }

    public function testCompactValueUiComponentCustomerNotExists()
    {
        $originValue = 'filename.ext1';

        $value = [
            'file' => 'filename.ext2',
            'name' => 'filename.ext2',
            'type' => 'image',
        ];

        $this->fileProcessorMock->expects($this->atLeastOnce())
            ->method('isExist')
            ->with(FileProcessor::TMP_DIR . '/' . $value['file'])
            ->willReturn(false);

        $model = $this->initialize([
            'value' => $originValue,
            'isAjax' => false,
            'entityTypeCode' => CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
        ]);

        $this->assertEquals($originValue, $model->compactValue($value));
    }
}
