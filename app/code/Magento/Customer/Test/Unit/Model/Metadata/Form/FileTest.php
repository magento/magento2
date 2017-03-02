<?php
/**
 * Magento\Customer\Model\Metadata\Form\File
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Model\Metadata\Form;

use Magento\Customer\Model\Metadata\ElementFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\MediaStorage\Model\File\Validator\NotProtectedExtension;

class FileTest extends AbstractFormTestCase
{
    const ENTITY_TYPE = 0;

    /** @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\Url\EncoderInterface */
    protected $urlEncode;

    /** @var \PHPUnit_Framework_MockObject_MockObject | NotProtectedExtension */
    protected $fileValidatorMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\Filesystem */
    protected $fileSystemMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\App\Request\Http */
    protected $requestMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\File\UploaderFactory
     */
    protected $uploaderFactoryMock;

    /**
     * @var \Magento\Customer\Model\FileProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $fileProcessorMock;

    protected function setUp()
    {
        parent::setUp();
        $this->urlEncode = $this->getMockBuilder(\Magento\Framework\Url\EncoderInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->fileValidatorMock = $this->getMockBuilder(
            \Magento\MediaStorage\Model\File\Validator\NotProtectedExtension::class
        )->disableOriginalConstructor()->getMock();
        $this->fileSystemMock = $this->getMockBuilder(\Magento\Framework\Filesystem::class)
            ->disableOriginalConstructor()->getMock();
        $this->requestMock = $this->getMockBuilder(\Magento\Framework\App\Request\Http::class)
            ->disableOriginalConstructor()->getMock();
        $this->uploaderFactoryMock = $this->getMock(\Magento\Framework\File\UploaderFactory::class, [], [], '', false);

        $this->fileProcessorMock = $this->getMockBuilder(\Magento\Customer\Model\FileProcessor::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @param array|bool $expected
     * @param string $attributeCode
     * @param bool $isAjax
     * @param string $delete
     * @dataProvider extractValueNoRequestScopeDataProvider
     */
    public function testExtractValueNoRequestScope($expected, $attributeCode = '', $delete = '')
    {
        $value = 'value';

        $this->requestMock->expects(
            $this->any()
        )->method(
            'getParam'
        )->will(
            $this->returnValue(['delete' => $delete])
        );

        $this->attributeMetadataMock->expects(
            $this->any()
        )->method(
            'getAttributeCode'
        )->will(
            $this->returnValue($attributeCode)
        );
        if (!empty($attributeCode)) {
            $_FILES[$attributeCode] = ['attributeCodeValue'];
        }

        $model = $this->initialize([
            'value' => $value,
            'isAjax' => false,
            'entityTypeCode' => self::ENTITY_TYPE,
        ]);

        $this->assertEquals($expected, $model->extractValue($this->requestMock));
        if (!empty($attributeCode)) {
            unset($_FILES[$attributeCode]);
        }
    }

    public function extractValueNoRequestScopeDataProvider()
    {
        return [
            'no_file' => [[]],
            'delete' => [['delete' => true], '', true],
            'file_delete' => [['attributeCodeValue', 'delete' => true], 'attributeCode', true],
            'file_!delete' => [['attributeCodeValue'], 'attributeCode', false]
        ];
    }

    /**
     * @param array $expected
     * @param string $requestScope
     * @param $mainScope
     * @dataProvider extractValueWithRequestScopeDataProvider
     */
    public function testExtractValueWithRequestScope($expected, $requestScope, $mainScope = false)
    {
        $value = 'value';

        $this->requestMock->expects(
            $this->any()
        )->method(
            'getParam'
        )->will(
            $this->returnValue(['delete' => true])
        );
        $this->requestMock->expects(
            $this->any()
        )->method(
            'getParams'
        )->will(
            $this->returnValue(['delete' => true])
        );

        $this->attributeMetadataMock->expects(
            $this->any()
        )->method(
            'getAttributeCode'
        )->will(
            $this->returnValue('attributeCode')
        );

        $model = $this->initialize([
            'value' => $value,
            'isAjax' => false,
            'entityTypeCode' => self::ENTITY_TYPE,
        ]);

        $model->setRequestScope($requestScope);

        if ($mainScope) {
            $_FILES['mainScope'] = $mainScope;
        }
        $this->assertEquals($expected, $model->extractValue($this->requestMock));
        if ($mainScope) {
            unset($_FILES['mainScope']);
        }
    }

    public function extractValueWithRequestScopeDataProvider()
    {
        return [
            'requestScope' => [[], 'requestScope'],
            'mainScope' => [
                ['fileKey' => 'attributeValue'],
                'mainScope',
                ['fileKey' => ['attributeCode' => 'attributeValue']],
            ],
            'mainScope/scopeName' => [
                ['fileKey' => 'attributeValue'],
                'mainScope/scopeName',
                ['fileKey' => ['scopeName' => ['attributeCode' => 'attributeValue']]],
            ]
        ];
    }

    /**
     * @param array|bool $expected
     * @param array $value
     * @param bool $isAjax
     * @param bool $isRequired
     * @dataProvider validateValueNotToUploadDataProvider
     */
    public function testValidateValueNotToUpload($expected, $value, $isAjax = false, $isRequired = true)
    {
        $this->attributeMetadataMock->expects(
            $this->any()
        )->method(
            'isRequired'
        )->will(
            $this->returnValue($isRequired)
        );
        $this->attributeMetadataMock->expects(
            $this->any()
        )->method(
            'getStoreLabel'
        )->will(
            $this->returnValue('attributeLabel')
        );

        $model = $this->initialize([
            'value' => $value,
            'isAjax' => $isAjax,
            'entityTypeCode' => self::ENTITY_TYPE,
        ]);

        $this->assertEquals($expected, $model->validateValue($value));
    }

    public function validateValueNotToUploadDataProvider()
    {
        return [
            'emptyValue' => [true, [], true],
            'someValue' => [true, ['some value']],
            'delete_someValue' => [true, ['delete' => true, 'some value'], false, false],
            'null' => [['"attributeLabel" is a required value.'], null]
        ];
    }

    /**
     * @param array $expected
     * @param array $value
     * @param array $parameters
     * @dataProvider validateValueToUploadDataProvider
     */
    public function testValidateValueToUpload($expected, $value, $parameters = [])
    {
        $parameters = array_merge(['uploaded' => true, 'valid' => true], $parameters);

        $this->attributeMetadataMock->expects($this->any())->method('isRequired')->will($this->returnValue(false));
        $this->attributeMetadataMock->expects(
            $this->any()
        )->method(
            'getStoreLabel'
        )->will(
            $this->returnValue('File Input Field Label')
        );

        $this->fileValidatorMock->expects(
            $this->any()
        )->method(
            'getMessages'
        )->will(
            $this->returnValue(['Validation error message.'])
        );
        $this->fileValidatorMock->expects(
            $this->any()
        )->method(
            'isValid'
        )->will(
            $this->returnValue($parameters['valid'])
        );

        $this->fileProcessorMock->expects($this->any())
            ->method('isExist')
            ->willReturn($parameters['uploaded']);

        $model = $this->initialize([
            'value' => $value,
            'isAjax' => false,
            'entityTypeCode' => self::ENTITY_TYPE,
        ]);

        $this->assertEquals($expected, $model->validateValue($value));
    }

    public function validateValueToUploadDataProvider()
    {
        return [
            'notValid' => [
                ['Validation error message.'],
                ['tmp_name' => 'tempName_0001.bin', 'name' => 'realFileName.bin'],
                ['valid' => false],
            ],
            'notUploaded' => [
                ['"realFileName.bin" is not a valid file.'],
                ['tmp_name' => 'tempName_0001.bin', 'name' => 'realFileName.bin'],
                ['uploaded' => false],
            ],
            'isValid' => [true, ['tmp_name' => 'tempName_0001.txt', 'name' => 'realFileName.txt']]
        ];
    }

    public function testCompactValueIsAjax()
    {
        $model = $this->initialize([
            'value' => 'value',
            'isAjax' => true,
            'entityTypeCode' => self::ENTITY_TYPE,
        ]);

        $this->assertSame($model, $model->compactValue('aValue'));
    }

    public function testCompactValueNoDelete()
    {
        $this->attributeMetadataMock->expects($this->any())->method('isRequired')->will($this->returnValue(false));

        $model = $this->initialize([
            'value' => 'value',
            'isAjax' => false,
            'entityTypeCode' => self::ENTITY_TYPE,
        ]);

        $this->fileProcessorMock->expects($this->once())
            ->method('removeUploadedFile')
            ->with('value')
            ->willReturnSelf();

        $this->assertSame([], $model->compactValue([]));
    }

    public function testCompactValueDelete()
    {
        $this->attributeMetadataMock->expects($this->any())->method('isRequired')->will($this->returnValue(false));

        $mediaDirMock = $this->getMockForAbstractClass(
            \Magento\Framework\Filesystem\Directory\WriteInterface::class
        );
        $mediaDirMock->expects($this->once())
            ->method('delete')
            ->with(self::ENTITY_TYPE . '/' . 'value');

        $this->fileSystemMock->expects($this->once())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::MEDIA)
            ->will($this->returnValue($mediaDirMock));

        $model = $this->initialize([
            'value' => 'value',
            'isAjax' => false,
            'entityTypeCode' => self::ENTITY_TYPE,
        ]);

        $this->assertSame('', $model->compactValue(['delete' => true]));
    }

    public function testCompactValueTmpFile()
    {
        $value = ['tmp_name' => 'tmp.file', 'name' => 'new.file'];
        $expected = 'saved.file';

        $mediaDirMock = $this->getMockForAbstractClass(
            \Magento\Framework\Filesystem\Directory\WriteInterface::class
        );
        $this->fileSystemMock->expects($this->once())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::MEDIA)
            ->will($this->returnValue($mediaDirMock));
        $mediaDirMock->expects($this->any())
            ->method('getAbsolutePath')
            ->will($this->returnArgument(0));
        $uploaderMock = $this->getMock(\Magento\Framework\File\Uploader::class, [], [], '', false);
        $this->uploaderFactoryMock->expects($this->once())
            ->method('create')
            ->with(['fileId' => $value])
            ->will($this->returnValue($uploaderMock));
        $uploaderMock->expects($this->once())
            ->method('setFilesDispersion')
            ->with(true);
        $uploaderMock->expects($this->once())
            ->method('setFilenamesCaseSensitivity')
            ->with(false);
        $uploaderMock->expects($this->once())
            ->method('setAllowRenameFiles')
            ->with(true);
        $uploaderMock->expects($this->once())
            ->method('save')
            ->with(self::ENTITY_TYPE, 'new.file');
        $uploaderMock->expects($this->once())
            ->method('getUploadedFileName')
            ->will($this->returnValue($expected));

        $model = $this->initialize([
            'value' => null,
            'isAjax' => false,
            'entityTypeCode' => self::ENTITY_TYPE,
        ]);

        $this->assertSame($expected, $model->compactValue($value));
    }

    public function testRestoreValue()
    {
        $value = 'value';

        $model = $this->initialize([
            'value' => $value,
            'isAjax' => false,
            'entityTypeCode' => self::ENTITY_TYPE,
        ]);

        $this->assertEquals($value, $model->restoreValue('aValue'));
    }

    /**
     * @param string $format
     * @dataProvider outputValueDataProvider
     */
    public function testOutputValueNonJson($format)
    {
        $model = $this->initialize([
            'value' => 'value',
            'isAjax' => false,
            'entityTypeCode' => self::ENTITY_TYPE,
        ]);

        $this->assertSame('', $model->outputValue($format));
    }

    public function outputValueDataProvider()
    {
        return [
            ElementFactory::OUTPUT_FORMAT_TEXT => [ElementFactory::OUTPUT_FORMAT_TEXT],
            ElementFactory::OUTPUT_FORMAT_ARRAY => [ElementFactory::OUTPUT_FORMAT_ARRAY],
            ElementFactory::OUTPUT_FORMAT_HTML => [ElementFactory::OUTPUT_FORMAT_HTML],
            ElementFactory::OUTPUT_FORMAT_ONELINE => [ElementFactory::OUTPUT_FORMAT_ONELINE],
            ElementFactory::OUTPUT_FORMAT_PDF => [ElementFactory::OUTPUT_FORMAT_PDF]
        ];
    }

    public function testOutputValueJson()
    {
        $value = 'value';
        $urlKey = 'url_key';

        $this->urlEncode->expects(
            $this->once()
        )->method(
            'encode'
        )->with(
            $this->equalTo($value)
        )->will(
            $this->returnValue($urlKey)
        );

        $expected = ['value' => $value, 'url_key' => $urlKey];

        $model = $this->initialize([
            'value' => $value,
            'isAjax' => false,
            'entityTypeCode' => self::ENTITY_TYPE,
        ]);

        $this->assertSame($expected, $model->outputValue(ElementFactory::OUTPUT_FORMAT_JSON));
    }

    /**
     * @param array $data
     * @return \Magento\Customer\Model\Metadata\Form\File
     */
    private function initialize(array $data)
    {
        $model = new \Magento\Customer\Model\Metadata\Form\File(
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
        );

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $objectManager->setBackwardCompatibleProperty(
            $model,
            'fileProcessor',
            $this->fileProcessorMock
        );

        return $model;
    }

    public function testExtractValueFileUploaderUIComponent()
    {
        $attributeCode = 'img1';
        $requestScope = 'customer';
        $fileName = 'filename.ext1';

        $this->attributeMetadataMock->expects($this->exactly(2))
            ->method('getAttributeCode')
            ->willReturn($attributeCode);

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with($requestScope)
            ->willReturn([
                $attributeCode => [
                    [
                        'file' => $fileName,
                    ],
                ],
            ]);

        $model = $this->initialize([
            'value' => 'value',
            'isAjax' => false,
            'entityTypeCode' => self::ENTITY_TYPE,
        ]);

        $model->setRequestScope($requestScope);
        $result = $model->extractValue($this->requestMock);

        $this->assertEquals(['file' => $fileName], $result);
    }

    public function testCompactValueRemoveUiComponentValue()
    {
        $value = 'value';

        $model = $this->initialize([
            'value' => $value,
            'isAjax' => false,
            'entityTypeCode' => self::ENTITY_TYPE,
        ]);

        $this->fileProcessorMock->expects($this->once())
            ->method('removeUploadedFile')
            ->with($value)
            ->willReturnSelf();

        $this->assertEquals([], $model->compactValue([]));
    }

    public function testCompactValueNoAction()
    {
        $value = 'value';

        $model = $this->initialize([
            'value' => $value,
            'isAjax' => false,
            'entityTypeCode' => self::ENTITY_TYPE,
        ]);

        $this->assertEquals($value, $model->compactValue($value));
    }

    public function testCompactValueUiComponent()
    {
        $value = [
            'file' => 'filename',
        ];

        $model = $this->initialize([
            'value' => null,
            'isAjax' => false,
            'entityTypeCode' => self::ENTITY_TYPE,
        ]);

        $this->fileProcessorMock->expects($this->once())
            ->method('moveTemporaryFile')
            ->with($value['file'])
            ->willReturn(true);

        $this->assertTrue($model->compactValue($value));
    }

    public function testCompactValueInputField()
    {
        $value = [
            'name' => 'filename.ext1',
            'tmp_name' => 'tmpfilename.ext1',
        ];

        $absolutePath = 'absolute_path';
        $uploadedFilename = 'filename.ext1';

        $mediaDirectoryMock = $this->getMockBuilder(
            \Magento\Framework\Filesystem\Directory\WriteInterface::class
        )
            ->getMockForAbstractClass();
        $mediaDirectoryMock->expects($this->once())
            ->method('getAbsolutePath')
            ->with(self::ENTITY_TYPE)
            ->willReturn($absolutePath);

        $this->fileSystemMock->expects($this->once())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::MEDIA)
            ->willReturn($mediaDirectoryMock);

        $uploaderMock = $this->getMockBuilder(
            \Magento\Framework\File\Uploader::class
        )->disableOriginalConstructor()->getMock();
        $uploaderMock->expects($this->once())
            ->method('setFilesDispersion')
            ->with(true)
            ->willReturnSelf();
        $uploaderMock->expects($this->once())
            ->method('setFilenamesCaseSensitivity')
            ->with(false)
            ->willReturnSelf();
        $uploaderMock->expects($this->once())
            ->method('setAllowRenameFiles')
            ->with(true)
            ->willReturnSelf();
        $uploaderMock->expects($this->once())
            ->method('save')
            ->with($absolutePath, $value['name'])
            ->willReturnSelf();
        $uploaderMock->expects($this->once())
            ->method('getUploadedFileName')
            ->willReturn($uploadedFilename);

        $this->uploaderFactoryMock->expects($this->once())
            ->method('create')
            ->with(['fileId' => $value])
            ->willReturn($uploaderMock);

        $model = $this->initialize([
            'value' => null,
            'isAjax' => false,
            'entityTypeCode' => self::ENTITY_TYPE,
        ]);

        $this->assertEquals($uploadedFilename, $model->compactValue($value));
    }

    public function testCompactValueInputFieldWithException()
    {
        $value = [
            'name' => 'filename.ext1',
            'tmp_name' => 'tmpfilename.ext1',
        ];

        $originValue = 'origin';

        $mediaDirectoryMock = $this->getMockBuilder(
            \Magento\Framework\Filesystem\Directory\WriteInterface::class
        )->getMockForAbstractClass();
        $mediaDirectoryMock->expects($this->once())
            ->method('delete')
            ->with(self::ENTITY_TYPE . '/' . $originValue);

        $this->fileSystemMock->expects($this->once())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::MEDIA)
            ->willReturn($mediaDirectoryMock);

        $exception = new \Exception('Error');

        $this->uploaderFactoryMock->expects($this->once())
            ->method('create')
            ->with(['fileId' => $value])
            ->willThrowException($exception);

        $this->loggerMock->expects($this->once())
            ->method('critical')
            ->with($exception)
            ->willReturnSelf();

        $model = $this->initialize([
            'value' => $originValue,
            'isAjax' => false,
            'entityTypeCode' => self::ENTITY_TYPE,
        ]);

        $this->assertEquals('', $model->compactValue($value));
    }
}
