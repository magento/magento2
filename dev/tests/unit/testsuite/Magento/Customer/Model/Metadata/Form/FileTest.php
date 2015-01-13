<?php
/**
 * Magento\Customer\Model\Metadata\Form\File
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Metadata\Form;

use Magento\Customer\Model\Metadata\ElementFactory;

class FileTest extends AbstractFormTestCase
{
    const ENTITY_TYPE = 0;
    /** @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\Url\EncoderInterface */
    protected $urlEncode;

    /** @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Core\Model\File\Validator\NotProtectedExtension */
    protected $fileValidatorMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\Filesystem */
    protected $fileSystemMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\App\RequestInterface */
    protected $requestMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\File\UploaderFactory
     */
    protected $uploaderFactoryMock;

    protected function setUp()
    {
        parent::setUp();
        $this->urlEncode = $this->getMockBuilder('Magento\Framework\Url\EncoderInterface')
            ->disableOriginalConstructor()->getMock();
        $this->fileValidatorMock = $this->getMockBuilder('Magento\Core\Model\File\Validator\NotProtectedExtension')
            ->disableOriginalConstructor()->getMock();
        $this->fileSystemMock = $this->getMockBuilder('Magento\Framework\Filesystem')
            ->disableOriginalConstructor()->getMock();
        $this->requestMock = $this->getMockBuilder('Magento\Framework\App\RequestInterface')
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getParam',
                    'getParams',
                    'getModuleName',
                    'setModuleName',
                    'getActionName',
                    'setActionName',
                    'getCookie',
                ]
            )
            ->getMock();
        $this->uploaderFactoryMock = $this->getMock('Magento\Framework\File\UploaderFactory', [], [], '', false);
    }

    /**
     * @param array|bool $expected
     * @param string $attributeCode
     * @param bool $isAjax
     * @param string $delete
     * @dataProvider extractValueNoRequestScopeDataProvider
     */
    public function testExtractValueNoRequestScope($expected, $attributeCode = '', $isAjax = false, $delete = '')
    {
        $value = 'value';
        $fileForm = $this->getClass($value, $isAjax);

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
        $this->assertEquals($expected, $fileForm->extractValue($this->requestMock));
        if (!empty($attributeCode)) {
            unset($_FILES[$attributeCode]);
        }
    }

    public function extractValueNoRequestScopeDataProvider()
    {
        return [
            'ajax' => [false, '', true],
            'no_file' => [[]],
            'delete' => [['delete' => true], '', false, true],
            'file_delete' => [['attributeCodeValue', 'delete' => true], 'attributeCode', false, true],
            'file_!delete' => [['attributeCodeValue'], 'attributeCode', false, false]
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
        $fileForm = $this->getClass($value, false);

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

        $fileForm->setRequestScope($requestScope);

        if ($mainScope) {
            $_FILES['mainScope'] = $mainScope;
        }
        $this->assertEquals($expected, $fileForm->extractValue($this->requestMock));
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
        $fileForm = $this->getClass($value, $isAjax);
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

        $this->assertEquals($expected, $fileForm->validateValue($value));
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
        $fileForm = $this->getClass($value, false);
        $fileForm->expects($this->any())->method('_isUploadedFile')->will($this->returnValue($parameters['uploaded']));
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
        $this->assertEquals($expected, $fileForm->validateValue($value));
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
        $fileForm = $this->getClass('value', true);
        $this->assertSame($fileForm, $fileForm->compactValue('aValue'));
    }

    public function testCompactValueNoDelete()
    {
        $fileForm = $this->getClass('value', false);
        $this->attributeMetadataMock->expects($this->any())->method('isRequired')->will($this->returnValue(false));
        $this->assertSame('value', $fileForm->compactValue([]));
    }

    public function testCompactValueDelete()
    {
        $fileForm = $this->getClass('value', false);
        $this->attributeMetadataMock->expects($this->any())->method('isRequired')->will($this->returnValue(false));
        $mediaDirMock = $this->getMockForAbstractClass('\Magento\Framework\Filesystem\Directory\WriteInterface');
        $mediaDirMock->expects($this->once())
            ->method('delete')
            ->with(self::ENTITY_TYPE . 'value');
        $this->fileSystemMock->expects($this->once())
            ->method('getDirectoryWrite')
            ->with(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)
            ->will($this->returnValue($mediaDirMock));
        $this->assertSame('', $fileForm->compactValue(['delete' => true]));
    }

    public function testCompactValueTmpFile()
    {
        $value = ['tmp_name' => 'tmp.file', 'name' => 'new.file'];
        $expected = 'saved.file';

        $fileForm = $this->getClass(null, false);
        $mediaDirMock = $this->getMockForAbstractClass('\Magento\Framework\Filesystem\Directory\WriteInterface');
        $this->fileSystemMock->expects($this->once())
            ->method('getDirectoryWrite')
            ->with(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)
            ->will($this->returnValue($mediaDirMock));
        $mediaDirMock->expects($this->any())
            ->method('getAbsolutePath')
            ->will($this->returnArgument(0));
        $uploaderMock = $this->getMock('\Magento\Framework\File\Uploader', [], [], '', false);
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

        $this->assertSame($expected, $fileForm->compactValue($value));
    }

    public function testRestoreValue()
    {
        $value = 'value';
        $fileForm = $this->getClass($value, false);
        $this->assertEquals($value, $fileForm->restoreValue('aValue'));
    }

    /**
     * @param string $format
     * @dataProvider outputValueDataProvider
     */
    public function testOutputValueNonJson($format)
    {
        $fileForm = $this->getClass('value', false);
        $this->assertSame('', $fileForm->outputValue($format));
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
        $fileForm = $this->getClass($value, false);
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
        $this->assertSame($expected, $fileForm->outputValue(ElementFactory::OUTPUT_FORMAT_JSON));
    }

    /**
     * Helper for creating the unit under test.
     *
     * @param string|int|bool|null $value The value undergoing testing by a given test
     * @param bool $isAjax
     * @return \PHPUnit_Framework_MockObject_MockObject | File
     */
    protected function getClass($value, $isAjax)
    {
        $fileForm = $this->getMock(
            'Magento\Customer\Model\Metadata\Form\File',
            ['_isUploadedFile'],
            [
                $this->localeMock,
                $this->loggerMock,
                $this->attributeMetadataMock,
                $this->localeResolverMock,
                $value,
                self::ENTITY_TYPE,
                $isAjax,
                $this->urlEncode,
                $this->fileValidatorMock,
                $this->fileSystemMock,
                $this->uploaderFactoryMock
            ]
        );
        return $fileForm;
    }
}
