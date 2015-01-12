<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\Attribute\Data;

class FileTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Eav\Model\Attribute\Data\File
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Url\EncoderInterface
     */
    protected $urlEncoder;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $fileValidatorMock;

    protected function setUp()
    {
        $timezoneMock = $this->getMock('\Magento\Framework\Stdlib\DateTime\TimezoneInterface');
        $loggerMock = $this->getMock('\Psr\Log\LoggerInterface', [], [], '', false);
        $localeResolverMock = $this->getMock('\Magento\Framework\Locale\ResolverInterface');
        $this->urlEncoder = $this->getMock('Magento\Framework\Url\EncoderInterface', [], [], '', false);
        $this->fileValidatorMock = $this->getMock(
            '\Magento\Core\Model\File\Validator\NotProtectedExtension', ['isValid', 'getMessages'], [], '', false
        );
        $filesystemMock = $this->getMock('\Magento\Framework\Filesystem', [], [], '', false);

        $this->model = new File(
            $timezoneMock, $loggerMock, $localeResolverMock,
            $this->urlEncoder, $this->fileValidatorMock, $filesystemMock
        );
    }

    /**
     * @covers \Magento\Eav\Model\Attribute\Data\File::outputValue
     *
     * @param string $format
     * @param mixed $value
     * @param mixed $expectedResult
     * @param int $callTimes
     * @dataProvider outputValueDataProvider
     */
    public function testOutputValue($format, $value, $callTimes, $expectedResult)
    {
        $entityMock = $this->getMock('\Magento\Framework\Model\AbstractModel', [], [], '', false);
        $entityMock->expects($this->once())->method('getData')->will($this->returnValue($value));

        $attributeMock = $this->getMock('\Magento\Eav\Model\Attribute', [], [], '', false);
        $this->urlEncoder->expects($this->exactly($callTimes))
            ->method('encode')
            ->will($this->returnValue('url_key'));

        $this->model->setEntity($entityMock);
        $this->model->setAttribute($attributeMock);
        $this->assertEquals($expectedResult, $this->model->outputValue($format));
    }

    /**
     * @return array
     */
    public function outputValueDataProvider()
    {
        return [
            [
                'format' => \Magento\Eav\Model\AttributeDataFactory::OUTPUT_FORMAT_JSON,
                'value' => 'value',
                'callTimes' => 1,
                'expectedResult' => ['value' => 'value', 'url_key' => 'url_key'],
            ],
            [
                'format' => \Magento\Eav\Model\AttributeDataFactory::OUTPUT_FORMAT_TEXT,
                'value' => 'value',
                'callTimes' => 0,
                'expectedResult' => ''
            ],
            [
                'format' => \Magento\Eav\Model\AttributeDataFactory::OUTPUT_FORMAT_TEXT,
                'value' => false,
                'callTimes' => 0,
                'expectedResult' => ''
            ],
        ];
    }

    /**
     * @covers \Magento\Eav\Model\Attribute\Data\File::validateValue
     * @covers \Magento\Eav\Model\Attribute\Data\File::_validateByRules
     *
     * @param mixed $value
     * @param mixed $originalValue
     * @param bool $isRequired
     * @param bool $isAjaxRequest
     * @param array $rules
     * @param bool $fileIsValid
     * @param array $expectedResult
     * @dataProvider validateValueDataProvider
     */
    public function testValidateValue(
        $value, $originalValue, $isRequired, $isAjaxRequest, $rules, $fileIsValid, $expectedResult
    ) {
        $entityMock = $this->getMock('\Magento\Framework\Model\AbstractModel', [], [], '', false);
        $entityMock->expects($this->any())->method('getData')->will($this->returnValue($originalValue));

        $attributeMock = $this->getMock('\Magento\Eav\Model\Attribute', [], [], '', false);
        $attributeMock->expects($this->any())->method('getStoreLabel')->will($this->returnValue('Label'));
        $attributeMock->expects($this->any())->method('getIsRequired')->will($this->returnValue($isRequired));
        $attributeMock->expects($this->any())->method('getIsAjaxRequest')->will($this->returnValue($isAjaxRequest));
        $attributeMock->expects($this->any())->method('getValidateRules')->will($this->returnValue($rules));

        $this->fileValidatorMock->expects($this->any())->method('isValid')->will($this->returnValue($fileIsValid));
        $this->fileValidatorMock->expects($this->any())->method('getMessages')->will($this->returnValue(['m1', 'm2']));

        $this->model->setEntity($entityMock);
        $this->model->setAttribute($attributeMock);
        $this->model->setIsAjaxRequest($isAjaxRequest);
        $this->assertEquals($expectedResult, $this->model->validateValue($value));
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function validateValueDataProvider()
    {
        return [
            [
                'value' => false,
                'originalValue' => false,
                'isRequired' => true,
                'isAjaxRequest' => true,
                'rules' => [],
                'fileIsValid' => true,
                'expectedResult' => true,
            ],
            [
                'value' => ['delete' => '', 'tmp_name' => ''],
                'originalValue' => 'value',
                'isRequired' => true,
                'isAjaxRequest' => false,
                'rules' => [],
                'fileIsValid' => true,
                'expectedResult' => true
            ],
            [
                'value' => ['delete' => 'delete', 'tmp_name' => ''],
                'originalValue' => 'value',
                'isRequired' => false,
                'isAjaxRequest' => false,
                'rules' => [],
                'fileIsValid' => true,
                'expectedResult' => true
            ],
            [
                'value' => ['delete' => 'delete', 'tmp_name' => ''],
                'originalValue' => 'value',
                'isRequired' => true,
                'isAjaxRequest' => false,
                'rules' => [],
                'fileIsValid' => true,
                'expectedResult' => ['"Label" is a required value.']
            ],
            [
                'value' => [
                    'delete' => 'delete', 'tmp_name' => 'tmp_name', 'name' => 'name',
                ],
                'originalValue' => 'value',
                'isRequired' => true,
                'isAjaxRequest' => false,
                'rules' => [],
                'fileIsValid' => true,
                'expectedResult' => true
            ],
            [
                'value' => [
                    'delete' => 'delete',
                    'tmp_name' => 'tmp_name',
                    'name' => 'name.txt',
                ],
                'originalValue' => 'value',
                'isRequired' => true,
                'isAjaxRequest' => false,
                'rules' => ['file_extensions' => 'txt,png'],
                'fileIsValid' => true,
                'expectedResult' => true
            ],
            [
                'value' => [
                    'delete' => 'delete',
                    'tmp_name' => 'tmp_name',
                    'name' => 'name.rpg',
                ],
                'originalValue' => 'value',
                'isRequired' => true,
                'isAjaxRequest' => false,
                'rules' => ['file_extensions' => ' txt , png '],
                'fileIsValid' => true,
                'expectedResult' => ['"Label" is not a valid file extension.']
            ],
            [
                'value' => [
                    'delete' => 'delete',
                    'tmp_name' => 'tmp_name',
                    'name' => 'name.txt',
                ],
                'originalValue' => 'value',
                'isRequired' => true,
                'isAjaxRequest' => false,
                'rules' => ['file_extensions' => ''],
                'fileIsValid' => false,
                'expectedResult' => ['m1', 'm2']
            ],
            [
                'value' => [
                    'delete' => 'delete',
                    'tmp_name' => 'NotUploaded',
                    'name' => '',
                ],
                'originalValue' => 'value',
                'isRequired' => true,
                'isAjaxRequest' => false,
                'rules' => [],
                'fileIsValid' => true,
                'expectedResult' => ['"Label" is not a valid file.']
            ],
            [
                'value' => [
                    'delete' => 'delete',
                    'tmp_name' => 'tmp_name',
                    'name' => 'name.txt',
                    'size' => 20,
                ],
                'originalValue' => 'value',
                'isRequired' => true,
                'isAjaxRequest' => false,
                'rules' => ['max_file_size' => 10],
                'fileIsValid' => true,
                'expectedResult' => ['"Label" exceeds the allowed file size.']
            ],
            [
                'value' => [
                    'delete' => 'delete',
                    'tmp_name' => 'tmp_name',
                    'name' => 'name.txt',
                    'size' => 5,
                ],
                'originalValue' => 'value',
                'isRequired' => true,
                'isAjaxRequest' => false,
                'rules' => ['max_file_size' => 10],
                'fileIsValid' => true,
                'expectedResult' => true
            ],
        ];
    }
};

/**
 * Mocking of std function to test validation
 *
 * @param string $name
 * @return bool
 */
function is_uploaded_file($name)
{
    return ($name == 'NotUploaded') ? false : true;
}
