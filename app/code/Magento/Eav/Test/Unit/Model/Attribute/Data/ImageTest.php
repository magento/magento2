<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Eav\Test\Unit\Model\Attribute\Data;

use Magento\Eav\Model\Attribute;
use Magento\Eav\Model\Attribute\Data\File;
use Magento\Eav\Model\Attribute\Data\Image;
use Magento\Framework\Filesystem;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Url\EncoderInterface;
use Magento\MediaStorage\Model\File\Validator\NotProtectedExtension;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class ImageTest extends TestCase
{
    /**
     * @var File
     */
    protected $model;

    protected function setUp(): void
    {
        $this->markTestSkipped('MAGETWO-34751: Test fails after being moved.  Might have hidden dependency.');
        $timezoneMock = $this->getMockForAbstractClass(TimezoneInterface::class);
        $loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);
        $localeResolverMock = $this->getMockForAbstractClass(ResolverInterface::class);
        $urlEncoder = $this->getMockForAbstractClass(EncoderInterface::class);
        $fileValidatorMock = $this->createMock(NotProtectedExtension::class);
        $filesystemMock = $this->createMock(Filesystem::class);

        $this->model = new Image(
            $timezoneMock,
            $loggerMock,
            $localeResolverMock,
            $urlEncoder,
            $fileValidatorMock,
            $filesystemMock
        );
    }

    /**
     * Attention: this test depends on mock of "is_uploaded_file" function in ./FileTest.php,
     * so validates method successfully in batch run of directory tests, separately will fail.
     *
     * @covers \Magento\Eav\Model\Attribute\Data\Image::_validateByRules
     *
     * @param mixed $value
     * @param mixed $originalValue
     * @param bool $isRequired
     * @param bool $isAjaxRequest
     * @param array $rules
     * @param array $expectedResult
     * @dataProvider validateValueDataProvider
     */
    public function testValidateValue(
        $value,
        $originalValue,
        $isRequired,
        $isAjaxRequest,
        $rules,
        $expectedResult
    ) {
        $entityMock = $this->createMock(AbstractModel::class);
        $entityMock->expects($this->any())->method('getData')->willReturn($originalValue);

        $attributeMock = $this->createMock(Attribute::class);
        $attributeMock->expects($this->any())->method('getStoreLabel')->willReturn('Label');
        $attributeMock->expects($this->any())->method('getIsRequired')->willReturn($isRequired);
        $attributeMock->expects($this->any())->method('getIsAjaxRequest')->willReturn($isAjaxRequest);
        $attributeMock->expects($this->any())->method('getValidateRules')->willReturn($rules);

        $this->model->setEntity($entityMock);
        $this->model->setAttribute($attributeMock);
        $this->model->setIsAjaxRequest($isAjaxRequest);
        $this->assertEquals($expectedResult, $this->model->validateValue($value));
    }

    /**
     * @return array
     */
    public static function validateValueDataProvider()
    {
        return [
            [
                'value' => ['delete' => 'delete', 'tmp_name' => 'NotUploaded'],
                'originalValue' => 'value',
                'isRequired' => true,
                'isAjaxRequest' => false,
                'rules' => [],
                'expectedResult' => ['"Label" is not a valid file'],
            ],
            [
                'value' => ['delete' => 'delete', 'tmp_name' => __DIR__ . '/_files/image.ico', 'name' => 'image.ico'],
                'originalValue' => 'value',
                'isRequired' => true,
                'isAjaxRequest' => false,
                'rules' => [],
                'expectedResult' => ['"Label" is not a valid image format']
            ],
            [
                'value' => ['delete' => 'delete', 'tmp_name' => __DIR__ . '/_files/image.jpg', 'name' => 'image.ppp'],
                'originalValue' => 'value',
                'isRequired' => true,
                'isAjaxRequest' => false,
                'rules' => [],
                'expectedResult' => true
            ],
            [
                'value' => [
                    'delete' => 'delete', 'tmp_name' => __DIR__ . '/_files/image.jpg',
                    'name' => 'image.jpg', 'size' => 10,
                ],
                'originalValue' => 'value',
                'isRequired' => true,
                'isAjaxRequest' => false,
                'rules' => ['max_file_size' => 2],
                'expectedResult' => ['"Label" exceeds the allowed file size.']
            ],
            [
                'value' => [
                    'delete' => 'delete', 'tmp_name' => __DIR__ . '/_files/image.jpg',
                    'name' => 'image.jpg', 'size' => 10,
                ],
                'originalValue' => 'value',
                'isRequired' => true,
                'isAjaxRequest' => false,
                'rules' => ['max_file_size' => 20],
                'expectedResult' => true
            ],
            [
                'value' => ['delete' => 'delete', 'tmp_name' => __DIR__ . '/_files/image.jpg', 'name' => 'image.jpg'],
                'originalValue' => 'value',
                'isRequired' => true,
                'isAjaxRequest' => false,
                'rules' => ['max_image_width' => 2],
                'expectedResult' => ['"Label" width exceeds allowed value of 2 px.']
            ],
            [
                'value' => ['delete' => 'delete', 'tmp_name' => __DIR__ . '/_files/image.jpg', 'name' => 'image.jpg'],
                'originalValue' => 'value',
                'isRequired' => true,
                'isAjaxRequest' => false,
                'rules' => ['max_image_width' => 2000],
                'expectedResult' => true
            ],
            [
                'value' => ['delete' => 'delete', 'tmp_name' => __DIR__ . '/_files/image.jpg', 'name' => 'image.jpg'],
                'originalValue' => 'value',
                'isRequired' => true,
                'isAjaxRequest' => false,
                'rules' => ['max_image_height' => 2],
                'expectedResult' => ['"Label" height exceeds allowed value of 2 px.']
            ],
            [
                'value' => ['delete' => 'delete', 'tmp_name' => __DIR__ . '/_files/image.jpg', 'name' => 'image.jpg'],
                'originalValue' => 'value',
                'isRequired' => true,
                'isAjaxRequest' => false,
                'rules' => ['max_image_height' => 2000],
                'expectedResult' => true
            ],
            [
                'value' => ['delete' => 'delete', 'tmp_name' => __DIR__ . '/_files/image.jpg', 'name' => 'image.jpg'],
                'originalValue' => 'value',
                'isRequired' => true,
                'isAjaxRequest' => false,
                'rules' => ['max_image_height' => 2, 'max_image_width' => 2],
                'expectedResult' => [
                    '"Label" width exceeds allowed value of 2 px.',
                    '"Label" height exceeds allowed value of 2 px.',
                ]
            ],
        ];
    }
}
