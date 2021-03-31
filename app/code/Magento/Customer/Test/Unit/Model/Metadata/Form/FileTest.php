<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model\Metadata\Form;

use Magento\Customer\Model\FileProcessor;
use Magento\Customer\Model\FileProcessorFactory;
use Magento\Customer\Model\Metadata\ElementFactory;
use Magento\Customer\Model\Metadata\Form\File;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\File\Uploader;
use Magento\Framework\File\UploaderFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Filesystem\Io\File as FileIo;
use Magento\Framework\Url\EncoderInterface;
use Magento\MediaStorage\Model\File\Validator\NotProtectedExtension;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FileTest extends AbstractFormTestCase
{
    const ENTITY_TYPE = 0;

    /**
     * @var MockObject|EncoderInterface
     */
    private $urlEncode;

    /**
     * @var MockObject|NotProtectedExtension
     */
    private $fileValidatorMock;

    /**
     * @var MockObject|Filesystem
     */
    private $fileSystemMock;

    /**
     * @var MockObject|Http
     */
    private $requestMock;

    /**
     * @var MockObject|UploaderFactory
     */
    private $uploaderFactoryMock;

    /**
     * @var FileProcessor|MockObject
     */
    private $fileProcessorMock;

    /**
     * @var FileProcessorFactory|MockObject
     */
    private $fileProcessorFactoryMock;

    /**
     * @var FileIo|MockObject
     */
    private $fileIoMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->urlEncode = $this->getMockBuilder(EncoderInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->fileValidatorMock = $this->getMockBuilder(NotProtectedExtension::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->fileSystemMock = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->requestMock = $this->getMockBuilder(Http::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->uploaderFactoryMock = $this->createMock(UploaderFactory::class);
        $this->fileProcessorMock = $this->getMockBuilder(FileProcessor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->fileProcessorFactoryMock = $this->getMockBuilder(FileProcessorFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->fileProcessorFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->fileProcessorMock);
        $this->fileIoMock = $this->createMock(FileIo::class);
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
        )->willReturn(
            ['delete' => $delete]
        );

        $this->attributeMetadataMock->expects(
            $this->any()
        )->method(
            'getAttributeCode'
        )->willReturn(
            $attributeCode
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

    /**
     * @return array
     */
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
        )->willReturn(
            ['delete' => true]
        );
        $this->requestMock->expects(
            $this->any()
        )->method(
            'getParams'
        )->willReturn(
            ['delete' => true]
        );

        $this->attributeMetadataMock->expects(
            $this->any()
        )->method(
            'getAttributeCode'
        )->willReturn(
            'attributeCode'
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

    /**
     * @return array
     */
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
        )->willReturn(
            $isRequired
        );
        $this->attributeMetadataMock->expects(
            $this->any()
        )->method(
            'getStoreLabel'
        )->willReturn(
            'attributeLabel'
        );

        $model = $this->initialize([
            'value' => $value,
            'isAjax' => $isAjax,
            'entityTypeCode' => self::ENTITY_TYPE,
        ]);

        $this->assertEquals($expected, $model->validateValue($value));
    }

    /**
     * @return array
     */
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

        $this->attributeMetadataMock->expects($this->any())->method('isRequired')->willReturn(false);
        $this->attributeMetadataMock->expects(
            $this->any()
        )->method(
            'getStoreLabel'
        )->willReturn(
            'File Input Field Label'
        );

        $this->fileValidatorMock->expects(
            $this->any()
        )->method(
            'getMessages'
        )->willReturn(
            ['Validation error message.']
        );
        $this->fileValidatorMock->expects(
            $this->any()
        )->method(
            'isValid'
        )->willReturn(
            $parameters['valid']
        );

        $this->fileIoMock->expects($this->any())
            ->method('getPathInfo')
            ->with($value['name'])
            ->willReturn([
                'extension' => $value['extension'],
                'basename' => $value['basename']
            ]);

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

    /**
     * @return array
     */
    public function validateValueToUploadDataProvider()
    {
        return [
            'notValid' => [
                ['Validation error message.'],
                [
                    'tmp_name' => 'tempName_0001.bin',
                    'name' => 'realFileName.bin',
                    'extension' => 'bin',
                    'basename' => 'realFileName.bin',
                ],
                ['valid' => false],
            ],
            'notUploaded' => [
                ['"realFileName.bin" is not a valid file.'],
                [
                    'tmp_name' => 'tempName_0001.bin',
                    'name' => 'realFileName.bin',
                    'extension' => 'bin',
                    'basename' => 'realFileName.bin',
                ],
                ['uploaded' => false],
            ],
            'isValid' => [
                true,
                [
                    'tmp_name' => 'tempName_0001.txt',
                    'name' => 'realFileName.txt',
                    'extension' => 'txt',
                    'basename' => 'realFileName.txt',
                ],
            ],
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
        $this->attributeMetadataMock->expects($this->any())->method('isRequired')->willReturn(false);

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
        $this->attributeMetadataMock->expects($this->any())->method('isRequired')->willReturn(false);

        $mediaDirMock = $this->getMockForAbstractClass(
            WriteInterface::class
        );
        $mediaDirMock->expects($this->once())
            ->method('delete')
            ->with(self::ENTITY_TYPE . '/' . 'value');

        $this->fileSystemMock->expects($this->once())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::MEDIA)
            ->willReturn($mediaDirMock);

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
            WriteInterface::class
        );
        $this->fileSystemMock->expects($this->once())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::MEDIA)
            ->willReturn($mediaDirMock);
        $mediaDirMock->expects($this->any())
            ->method('getAbsolutePath')
            ->willReturnArgument(0);
        $uploaderMock = $this->createMock(Uploader::class);
        $this->uploaderFactoryMock->expects($this->once())
            ->method('create')
            ->with(['fileId' => $value])
            ->willReturn($uploaderMock);
        $uploaderMock->expects($this->once())->method('getFileExtension')->willReturn('file');
        $this->fileValidatorMock->expects($this->once())
            ->method('isValid')
            ->with('file')
            ->willReturn(true);
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
            ->willReturn($expected);

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

    /**
     * @return array
     */
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
            $value
        )->willReturn(
            $urlKey
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
     * @return File
     */
    private function initialize(array $data)
    {
        return new File(
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
            $this->fileProcessorFactoryMock
        );
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
            WriteInterface::class
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
            Uploader::class
        )->disableOriginalConstructor()
            ->getMock();
        $uploaderMock->expects($this->once())->method('getFileExtension')->willReturn('ext1');
        $this->fileValidatorMock->expects($this->once())
            ->method('isValid')
            ->with('ext1')
            ->willReturn(true);
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
            WriteInterface::class
        )->getMockForAbstractClass();
        $mediaDirectoryMock->expects($this->once())
            ->method('delete')
            ->with(self::ENTITY_TYPE . '/' . $originValue);

        $this->fileSystemMock->expects($this->once())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::MEDIA)
            ->willReturn($mediaDirectoryMock);

        $exception = new \Exception('Error');

        $uploaderMock = $this->createMock(Uploader::class);
        $this->uploaderFactoryMock->expects($this->once())
            ->method('create')
            ->with(['fileId' => $value])
            ->willReturn($uploaderMock);
        $uploaderMock->expects($this->once())->method('getFileExtension')->willReturn('ext1');
        $this->fileValidatorMock->expects($this->once())
            ->method('isValid')
            ->with('ext1')
            ->willReturn(true);
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
            ->with(self::ENTITY_TYPE, $value['name'])
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

    /**
     * @return void
     */
    public function testCompactValueWithProtectedExtension(): void
    {
        $value = [
            'name' => 'filename.php',
            'tmp_name' => 'tmpfilename.php',
        ];

        $originValue = 'origin';

        $mediaDirectoryMock = $this->getMockBuilder(
            WriteInterface::class
        )->getMockForAbstractClass();
        $mediaDirectoryMock->expects($this->once())
            ->method('delete')
            ->with(self::ENTITY_TYPE . '/' . $originValue);

        $this->fileSystemMock->expects($this->once())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::MEDIA)
            ->willReturn($mediaDirectoryMock);

        $uploaderMock = $this->createMock(Uploader::class);
        $this->uploaderFactoryMock->expects($this->once())
            ->method('create')
            ->with(['fileId' => $value])
            ->willReturn($uploaderMock);
        $uploaderMock->expects($this->once())->method('getFileExtension')->willReturn('php');
        $this->fileValidatorMock->expects($this->once())
            ->method('isValid')
            ->with('php')
            ->willReturn(false);
        $this->fileValidatorMock->expects($this->once())
            ->method('getMessages')
            ->willReturn([
                'php' => __('File with an extension php is protected and cannot be uploaded'),
            ]);

        $model = $this->initialize([
            'value' => $originValue,
            'isAjax' => false,
            'entityTypeCode' => self::ENTITY_TYPE,
        ]);

        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('File with an extension php is protected and cannot be uploaded');

        $this->assertEquals('', $model->compactValue($value));
    }
}
