<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Eav\Test\Unit\Model\Attribute\Data;

use Magento\Eav\Model\Attribute;
use Magento\Eav\Model\Attribute\Data\File;
use Magento\Eav\Model\AttributeDataFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Io\File as FileIo;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Url\EncoderInterface;
use Magento\MediaStorage\Model\File\Validator\NotProtectedExtension;
use Psr\Log\LoggerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for Magento\Eav\Model\Attribute\Data\File class.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FileTest extends TestCase
{
    /**
     * @var File
     */
    protected $model;

    /**
     * @var MockObject|EncoderInterface
     */
    protected $urlEncoder;

    /**
     * @var MockObject
     */
    protected $fileValidatorMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $timezoneMock = $this->getMockForAbstractClass(TimezoneInterface::class);
        $loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);
        $localeResolverMock = $this->getMockForAbstractClass(ResolverInterface::class);
        $this->urlEncoder = $this->getMockForAbstractClass(EncoderInterface::class);
        $this->fileValidatorMock = $this->createPartialMock(
            NotProtectedExtension::class,
            ['isValid', 'getMessages']
        );
        $filesystemMock = $this->createMock(Filesystem::class);
        $fileIo = $this->createMock(FileIo::class);

        $this->model = new File(
            $timezoneMock,
            $loggerMock,
            $localeResolverMock,
            $this->urlEncoder,
            $this->fileValidatorMock,
            $filesystemMock,
            $fileIo
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
        $entityMock = $this->createMock(AbstractModel::class);
        $entityMock->expects($this->once())->method('getData')->willReturn($value);

        $attributeMock = $this->createMock(Attribute::class);
        $this->urlEncoder->expects($this->exactly($callTimes))
            ->method('encode')
            ->willReturn('url_key');

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
                'format' => AttributeDataFactory::OUTPUT_FORMAT_JSON,
                'value' => 'value',
                'callTimes' => 1,
                'expectedResult' => ['value' => 'value', 'url_key' => 'url_key'],
            ],
            [
                'format' => AttributeDataFactory::OUTPUT_FORMAT_TEXT,
                'value' => 'value',
                'callTimes' => 0,
                'expectedResult' => ''
            ],
            [
                'format' => AttributeDataFactory::OUTPUT_FORMAT_TEXT,
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
        $value,
        $originalValue,
        $isRequired,
        $isAjaxRequest,
        $rules,
        $fileIsValid,
        $expectedResult
    ) {
        $this->markTestSkipped('MAGETWO-34751: Test fails after being moved.  Might have hidden dependency.');
        $entityMock = $this->createMock(AbstractModel::class);
        $entityMock->expects($this->any())->method('getData')->willReturn($originalValue);

        $attributeMock = $this->createMock(Attribute::class);
        $attributeMock->expects($this->any())->method('getStoreLabel')->willReturn('Label');
        $attributeMock->expects($this->any())->method('getIsRequired')->willReturn($isRequired);
        $attributeMock->expects($this->any())->method('getIsAjaxRequest')->willReturn($isAjaxRequest);
        $attributeMock->expects($this->any())->method('getValidateRules')->willReturn($rules);

        $this->fileValidatorMock->expects($this->any())->method('isValid')->willReturn($fileIsValid);
        $this->fileValidatorMock->expects($this->any())->method('getMessages')->willReturn(['m1', 'm2']);

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
}

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
