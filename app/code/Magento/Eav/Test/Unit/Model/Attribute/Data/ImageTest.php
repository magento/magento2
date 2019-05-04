<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Eav\Test\Unit\Model\Attribute\Data;

class ImageTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Eav\Model\Attribute\Data\File
     */
    protected $model;

    protected function setUp()
    {
        $this->markTestSkipped('MAGETWO-34751: Test fails after being moved.  Might have hidden dependency.');
        $timezoneMock = $this->createMock(\Magento\Framework\Stdlib\DateTime\TimezoneInterface::class);
        $loggerMock = $this->createMock(\Psr\Log\LoggerInterface::class);
        $localeResolverMock = $this->createMock(\Magento\Framework\Locale\ResolverInterface::class);
        $urlEncoder = $this->createMock(\Magento\Framework\Url\EncoderInterface::class);
        $fileValidatorMock = $this->createMock(\Magento\MediaStorage\Model\File\Validator\NotProtectedExtension::class);
        $filesystemMock = $this->createMock(\Magento\Framework\Filesystem::class);

        $this->model = new \Magento\Eav\Model\Attribute\Data\Image(
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
        $entityMock = $this->createMock(\Magento\Framework\Model\AbstractModel::class);
        $entityMock->expects($this->any())->method('getData')->will($this->returnValue($originalValue));

        $attributeMock = $this->createMock(\Magento\Eav\Model\Attribute::class);
        $attributeMock->expects($this->any())->method('getStoreLabel')->will($this->returnValue('Label'));
        $attributeMock->expects($this->any())->method('getIsRequired')->will($this->returnValue($isRequired));
        $attributeMock->expects($this->any())->method('getIsAjaxRequest')->will($this->returnValue($isAjaxRequest));
        $attributeMock->expects($this->any())->method('getValidateRules')->will($this->returnValue($rules));

        $this->model->setEntity($entityMock);
        $this->model->setAttribute($attributeMock);
        $this->model->setIsAjaxRequest($isAjaxRequest);
        $this->assertEquals($expectedResult, $this->model->validateValue($value));
    }

    /**
     * @return array
     */
    public function validateValueDataProvider()
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
