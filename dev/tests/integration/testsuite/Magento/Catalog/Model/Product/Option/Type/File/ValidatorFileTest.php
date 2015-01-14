<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product\Option\Type\File;

/**
 * @magentoDataFixture Magento/Catalog/_files/validate_image.php
 */
class ValidatorFileTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ValidatorFile
     */
    protected $model;

    /**
     * @var \Magento\Framework\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Framework\HTTP\Adapter\FileTransferFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $httpFactoryMock;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->httpFactoryMock = $this->getMock('Magento\Framework\HTTP\Adapter\FileTransferFactory', ['create']);
        $this->model = $this->objectManager->create(
            'Magento\Catalog\Model\Product\Option\Type\File\ValidatorFile',
            [
                'httpFactory' => $this->httpFactoryMock
            ]
        );
    }

    /**
     * @expectedException \Magento\Catalog\Model\Product\Option\Type\File\RunValidationException
     * @return void
     */
    public function testRunValidationException()
    {
        $httpAdapterMock = $this->getMock('Zend_File_Transfer_Adapter_Http', ['isValid']);
        $this->httpFactoryMock->expects($this->once())->method('create')->will($this->returnValue($httpAdapterMock));

        $this->model->validate(
            $this->objectManager->create('Magento\Framework\Object'),
            $this->getProductOption(['is_require' => false])
        );
    }

    /**
     * @expectedException \Magento\Catalog\Model\Product\Option\Type\File\LargeSizeException
     * @expectedExceptionMessage The file you uploaded is larger than 2 Megabytes allowed by server
     * @return void
     */
    public function testLargeSizeException()
    {
        $this->prepareEnv();
        $_SERVER['CONTENT_LENGTH'] = 2097153;
        $httpAdapterMock = $this->getMock('Zend_File_Transfer_Adapter_Http', ['getFileInfo']);
        $exception = function () {
            throw new \Exception();
        };
        $httpAdapterMock->expects($this->once())->method('getFileInfo')->will($this->returnCallback($exception));
        $this->httpFactoryMock->expects($this->once())->method('create')->will($this->returnValue($httpAdapterMock));

        $this->model->validate(
            $this->objectManager->create('Magento\Framework\Object'),
            $this->getProductOption(['is_require' => false])
        );
    }

    /**
     * @expectedException \Magento\Catalog\Model\Product\Option\Type\File\OptionRequiredException
     * @return void
     */
    public function testOptionRequiredException()
    {
        $this->prepareEnv();
        $httpAdapterMock = $this->getMock('Zend_File_Transfer_Adapter_Http', ['getFileInfo']);
        $exception = function () {
            throw new \Exception();
        };
        $httpAdapterMock->expects($this->once())->method('getFileInfo')->will($this->returnCallback($exception));
        $this->httpFactoryMock->expects($this->once())->method('create')->will($this->returnValue($httpAdapterMock));

        $this->model->validate(
            $this->objectManager->create('Magento\Framework\Object'),
            $this->getProductOption(['is_require' => false])
        );
    }

    /**
     * @expectedException \Magento\Catalog\Model\Product\Option\Type\File\Exception
     * @expectedExceptionMessage Please specify the product's required option(s).
     * @return void
     */
    public function testException()
    {
        $this->prepareEnv();
        $httpAdapterMock = $this->getMock('Zend_File_Transfer_Adapter_Http', ['isUploaded']);
        $httpAdapterMock->expects($this->once())->method('isUploaded')->will($this->returnValue(false));
        $this->httpFactoryMock->expects($this->once())->method('create')->will($this->returnValue($httpAdapterMock));

        $this->model->validate(
            $this->objectManager->create('Magento\Framework\Object'),
            $this->getProductOption()
        );
    }

    /**
     * @return void
     */
    public function testInvalidateFile()
    {
        $this->setExpectedException(
            '\Magento\Catalog\Model\Product\Option\Type\File\Exception',
            "The file 'test.jpg' for 'MediaOption' has an invalid extension.\n"
            . "The file 'test.jpg' for 'MediaOption' has an invalid extension.\n"
            . "Maximum allowed image size for 'MediaOption' is 2000x2000 px.\n"
            . "The file 'test.jpg' you uploaded is larger than the 2 megabytes allowed by our server."
        );
        $this->prepareEnv();
        $httpAdapterMock = $this->getMock('Zend_File_Transfer_Adapter_Http', ['isValid', 'getErrors']);
        $httpAdapterMock->expects($this->once())->method('isValid')->will($this->returnValue(false));
        $httpAdapterMock->expects($this->exactly(2))->method('getErrors')->will($this->returnValue([
            \Zend_Validate_File_ExcludeExtension::FALSE_EXTENSION,
            \Zend_Validate_File_Extension::FALSE_EXTENSION,
            \Zend_Validate_File_ImageSize::WIDTH_TOO_BIG,
            \Zend_Validate_File_FilesSize::TOO_BIG,
        ]));
        $this->httpFactoryMock->expects($this->once())->method('create')->will($this->returnValue($httpAdapterMock));

        $this->model->validate(
            $this->objectManager->create('Magento\Framework\Object'),
            $this->getProductOption()
        );
    }

    /**
     * @return void
     */
    public function testValidate()
    {
        $this->prepareEnv();
        $httpAdapterMock = $this->getMock('Zend_File_Transfer_Adapter_Http', ['isValid']);
        $httpAdapterMock->expects($this->once())->method('isValid')->will($this->returnValue(true));
        $this->httpFactoryMock->expects($this->once())->method('create')->will($this->returnValue($httpAdapterMock));

        $result = $this->model->validate(
            $this->objectManager->create('Magento\Framework\Object'),
            $this->getProductOption()
        );
        unset($result['fullpath'], $result['secret_key']);
        $this->assertEquals($this->expectedValidate(), $result);
    }

    /**
     * @param array $options
     * @return \Magento\Catalog\Model\Product\Option
     */
    protected function getProductOption(array $options = [])
    {
        $data = [
            'option_id' => '1',
            'product_id' => '4',
            'type' => 'file',
            'is_require' => '1',
            'sku' => null,
            'max_characters' => null,
            'file_extension' => null,
            'image_size_x' => '2000',
            'image_size_y' => '2000',
            'sort_order' => '0',
            'default_title' => 'MediaOption',
            'store_title' => null,
            'title' => 'MediaOption',
            'default_price' => '5.0000',
            'default_price_type' => 'fixed',
            'store_price' => null,
            'store_price_type' => null,
            'price' => '5.0000',
            'price_type' => 'fixed',
        ];
        $option = $this->objectManager->create(
            'Magento\Catalog\Model\Product\Option',
            [
                'data' => array_merge($data, $options)
            ]
        );

        return $option;
    }

    /**
     * @return void
     */
    protected function prepareEnv()
    {
        $file     = 'magento_small_image.jpg';

        /** @var \Magento\Framework\Filesystem $filesystem */
        $filesystem = $this->objectManager->get('Magento\Framework\Filesystem');
        $tmpDirectory = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::SYS_TMP);
        $filePath = $tmpDirectory->getAbsolutePath($file);

        $_FILES['options_1_file'] = [
            'name' => 'test.jpg',
            'type' => 'image/jpeg',
            'tmp_name' => $filePath,
            'error' => 0,
            'size' => 12500,
        ];
        $_SERVER['CONTENT_LENGTH'] = 2097152;
    }

    /**
     * @return array
     */
    protected function expectedValidate()
    {
        return [
            'type' => 'image/jpeg',
            'title' => 'test.jpg',
            'quote_path' => 'pub/media/custom_options/quote/t/e/9c198b6663f1e032365b8cfedfe7ad77.jpg',
            'order_path' => 'pub/media/custom_options/order/t/e/9c198b6663f1e032365b8cfedfe7ad77.jpg',
            'size' => '6747',
            'width' => 136,
            'height' => 131,
        ];
    }
}
