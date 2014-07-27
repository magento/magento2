<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Eav\Model\Attribute\Data;

class ImageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Eav\Model\Attribute\Data\File
     */
    protected $model;

    protected function setUp()
    {
        $timezoneMock = $this->getMock('\Magento\Framework\Stdlib\DateTime\TimezoneInterface');
        $loggerMock = $this->getMock('\Magento\Framework\Logger', [], [], '', false);
        $localeResolverMock = $this->getMock('\Magento\Framework\Locale\ResolverInterface');
        $coreDataMock = $this->getMock('\Magento\Core\Helper\Data', [], [], '', false);
        $fileValidatorMock = $this->getMock(
            '\Magento\Core\Model\File\Validator\NotProtectedExtension', [], [], '', false
        );
        $filesystemMock = $this->getMock('\Magento\Framework\App\Filesystem', [], [], '', false);

        $this->model = new Image(
            $timezoneMock, $loggerMock, $localeResolverMock, $coreDataMock, $fileValidatorMock, $filesystemMock
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
        $value, $originalValue, $isRequired, $isAjaxRequest, $rules, $expectedResult
    ) {
        $entityMock = $this->getMock('\Magento\Framework\Model\AbstractModel', [], [], '', false);
        $entityMock->expects($this->any())->method('getData')->will($this->returnValue($originalValue));

        $attributeMock = $this->getMock('\Magento\Eav\Model\Attribute', [], [], '', false);
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
                'expectedResult' => ['"Label" is not a valid file']
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
                    'name' => 'image.jpg', 'size' => 10
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
                    'name' => 'image.jpg', 'size' => 10
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
                'rules' => ['max_image_heght' => 2],
                'expectedResult' => ['"Label" height exceeds allowed value of 2 px.']
            ],
            [
                'value' => ['delete' => 'delete', 'tmp_name' => __DIR__ . '/_files/image.jpg', 'name' => 'image.jpg'],
                'originalValue' => 'value',
                'isRequired' => true,
                'isAjaxRequest' => false,
                'rules' => ['max_image_heght' => 2000],
                'expectedResult' => true
            ],
            [
                'value' => ['delete' => 'delete', 'tmp_name' => __DIR__ . '/_files/image.jpg', 'name' => 'image.jpg'],
                'originalValue' => 'value',
                'isRequired' => true,
                'isAjaxRequest' => false,
                'rules' => ['max_image_heght' => 2, 'max_image_width' => 2],
                'expectedResult' => [
                    '"Label" width exceeds allowed value of 2 px.',
                    '"Label" height exceeds allowed value of 2 px.'
                ]
            ],
        ];
    }
};
