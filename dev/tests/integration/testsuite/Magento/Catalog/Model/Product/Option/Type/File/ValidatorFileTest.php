<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product\Option\Type\File;

/**
 * @magentoDataFixture Magento/Catalog/_files/validate_image.php
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ValidatorFileTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ValidatorFile
     */
    protected $model;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Magento\Framework\HTTP\Adapter\FileTransferFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $httpFactoryMock;

    /**
     * @var int
     */
    protected $maxFileSizeInMb;

    /**
     * @var int
     */
    protected $maxFileSize;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->httpFactoryMock = $this->createPartialMock(
            \Magento\Framework\HTTP\Adapter\FileTransferFactory::class,
            ['create']
        );
        /** @var \Magento\Framework\File\Size $fileSize */
        $fileSize = $this->objectManager->create(\Magento\Framework\File\Size::class);
        $this->maxFileSize = $fileSize->getMaxFileSize();
        $this->maxFileSizeInMb = $fileSize->getMaxFileSizeInMb();

        $this->model = $this->objectManager->create(
            \Magento\Catalog\Model\Product\Option\Type\File\ValidatorFile::class,
            [
                'httpFactory' => $this->httpFactoryMock
            ]
        );
    }

    /**
     * @expectedException \Magento\Framework\Validator\Exception
     * @return void
     */
    public function testRunValidationException()
    {
        $httpAdapterMock = $this->createPartialMock(\Zend_File_Transfer_Adapter_Http::class, ['isValid']);
        $this->httpFactoryMock->expects($this->once())->method('create')->will($this->returnValue($httpAdapterMock));

        $this->model->validate(
            $this->objectManager->create(\Magento\Framework\DataObject::class),
            $this->getProductOption(['is_require' => false])
        );
    }

    /**
     * @backupGlobals enabled
     * @return void
     */
    public function testLargeSizeFile()
    {
        $this->expectException(
            \Magento\Framework\Exception\LocalizedException::class,
            sprintf('The file you uploaded is larger than %s Megabytes allowed by server', $this->maxFileSizeInMb)
        );
        $this->prepareEnv();
        $_SERVER['CONTENT_LENGTH'] = $this->maxFileSize + 1;
        $httpAdapterMock = $this->createPartialMock(\Zend_File_Transfer_Adapter_Http::class, ['getFileInfo']);
        $exception = function () {
            throw new \Exception();
        };
        $httpAdapterMock->expects($this->once())->method('getFileInfo')->will($this->returnCallback($exception));
        $this->httpFactoryMock->expects($this->once())->method('create')->will($this->returnValue($httpAdapterMock));

        $property = new \ReflectionProperty($httpAdapterMock, '_files');
        $property->setAccessible(true);
        $property->setValue($httpAdapterMock, ['options_1_file' => $_FILES['options_1_file']]);
        $this->model->validate(
            $this->objectManager->create(\Magento\Framework\DataObject::class),
            $this->getProductOption(['is_require' => false])
        );
    }

    /**
     * @expectedException \Magento\Catalog\Model\Product\Exception
     * @return void
     */
    public function testOptionRequiredException()
    {
        $this->prepareEnv();
        $httpAdapterMock = $this->createPartialMock(\Zend_File_Transfer_Adapter_Http::class, ['getFileInfo']);
        $exception = function () {
            throw new \Exception();
        };
        $httpAdapterMock->expects($this->once())->method('getFileInfo')->will($this->returnCallback($exception));
        $this->httpFactoryMock->expects($this->once())->method('create')->will($this->returnValue($httpAdapterMock));

        $property = new \ReflectionProperty($httpAdapterMock, '_files');
        $property->setAccessible(true);
        $property->setValue($httpAdapterMock, ['options_1_file' => $_FILES['options_1_file']]);
        $this->model->validate(
            $this->objectManager->create(\Magento\Framework\DataObject::class),
            $this->getProductOption(['is_require' => false])
        );
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Please specify product's required option(s).
     * @return void
     */
    public function testException()
    {
        $this->prepareEnv();
        $httpAdapterMock = $this->createPartialMock(\Zend_File_Transfer_Adapter_Http::class, ['isUploaded']);
        $httpAdapterMock->expects($this->once())->method('isUploaded')->will($this->returnValue(false));
        $this->httpFactoryMock->expects($this->once())->method('create')->will($this->returnValue($httpAdapterMock));

        $property = new \ReflectionProperty($httpAdapterMock, '_files');
        $property->setAccessible(true);
        $property->setValue($httpAdapterMock, ['options_1_file' => $_FILES['options_1_file']]);
        $this->model->validate(
            $this->objectManager->create(\Magento\Framework\DataObject::class),
            $this->getProductOption()
        );
    }

    /**
     * @return void
     */
    public function testInvalidateFile()
    {
        $this->expectException(
            \Magento\Framework\Exception\LocalizedException::class,
            "The file 'test.jpg' for 'MediaOption' has an invalid extension.\n"
            . "The file 'test.jpg' for 'MediaOption' has an invalid extension.\n"
            . "The maximum allowed image size for 'MediaOption' is 2000x2000 px.\n"
            . sprintf(
                "The file 'test.jpg' you uploaded is larger than the %s megabytes allowed by our server.",
                $this->maxFileSizeInMb
            )
        );
        $this->prepareEnv();
        $httpAdapterMock = $this->createPartialMock(
            \Zend_File_Transfer_Adapter_Http::class,
            ['isValid', 'getErrors', 'getFileInfo', 'isUploaded']
        );
        $httpAdapterMock->expects($this->once())
            ->method('isValid')
            ->willReturn(false);
        $httpAdapterMock->expects($this->exactly(2))
            ->method('getErrors')
            ->willReturn(
                [
                    \Zend_Validate_File_ExcludeExtension::FALSE_EXTENSION,
                    \Zend_Validate_File_Extension::FALSE_EXTENSION,
                    \Zend_Validate_File_ImageSize::WIDTH_TOO_BIG,
                    \Zend_Validate_File_FilesSize::TOO_BIG,
                ]
            );
        $this->httpFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($httpAdapterMock);
        $httpAdapterMock->expects($this->once())
            ->method('isUploaded')
            ->willReturn(true);
        $this->model->validate(
            $this->objectManager->create(\Magento\Framework\DataObject::class),
            $this->getProductOption()
        );
    }

    /**
     * @return void
     */
    public function testValidate()
    {
        $this->prepareGoodEnv();
        $httpAdapterMock = $this->createPartialMock(\Zend_File_Transfer_Adapter_Http::class, ['isValid']);
        $httpAdapterMock->expects($this->once())->method('isValid')->will($this->returnValue(true));
        $this->httpFactoryMock->expects($this->once())->method('create')->will($this->returnValue($httpAdapterMock));

        $property = new \ReflectionProperty($httpAdapterMock, '_files');
        $property->setAccessible(true);
        $property->setValue($httpAdapterMock, ['options_1_file' => $_FILES['options_1_file']]);
        $result = $this->model->validate(
            $this->objectManager->create(\Magento\Framework\DataObject::class),
            $this->getProductOption()
        );
        unset($result['fullpath'], $result['secret_key']);
        $this->assertEquals($this->expectedValidate(), $result);
    }

    public function testEmptyFile()
    {
        $this->prepareEnvForEmptyFile();

        $this->expectException(
            \Magento\Framework\Exception\LocalizedException::class,
            'The file is empty. Please choose another one'
        );

        $httpAdapterMock = $this->createPartialMock(\Zend_File_Transfer_Adapter_Http::class, ['isValid']);
        $httpAdapterMock->expects($this->once())->method('isValid')->will($this->returnValue(true));
        $this->httpFactoryMock->expects($this->once())->method('create')->will($this->returnValue($httpAdapterMock));

        $property = new \ReflectionProperty($httpAdapterMock, '_files');
        $property->setAccessible(true);
        $property->setValue($httpAdapterMock, ['options_1_file' => $_FILES['options_1_file']]);
        $this->model->validate(
            $this->objectManager->create(\Magento\Framework\DataObject::class),
            $this->getProductOption()
        );
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
            \Magento\Catalog\Model\Product\Option::class,
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
        $file = 'magento_small_image.jpg';

        /** @var \Magento\Framework\Filesystem $filesystem */
        $filesystem = $this->objectManager->get(\Magento\Framework\Filesystem::class);
        $tmpDirectory = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::SYS_TMP);
        $filePath = $tmpDirectory->getAbsolutePath($file);

        $_FILES['options_1_file'] = [
            'name' => 'test.jpg',
            'type' => 'image/jpeg',
            'tmp_name' => $filePath,
            'error' => 0,
            'size' => 12500,
        ];
    }

    /**
     * @return void
     */
    protected function prepareGoodEnv()
    {
        $file = 'magento_small_image.jpg';

        /** @var \Magento\Framework\Filesystem $filesystem */
        $filesystem = $this->objectManager->get(\Magento\Framework\Filesystem::class);
        $tmpDirectory = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::SYS_TMP);
        $filePath = $tmpDirectory->getAbsolutePath($file);

        $_FILES['options_1_file'] = [
            'name' => 'test.jpg',
            'type' => 'image/jpeg',
            'tmp_name' => $filePath,
            'error' => 0,
            'size' => '3046',
        ];
    }

    /**
     * Test exception for empty file
     *
     * @return void
     */
    protected function prepareEnvForEmptyFile()
    {
        $file = 'magento_empty.jpg';

        /** @var \Magento\Framework\Filesystem $filesystem */
        $filesystem = $this->objectManager->get(\Magento\Framework\Filesystem::class);
        $tmpDirectory = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::SYS_TMP);
        $filePath = $tmpDirectory->getAbsolutePath($file);

        $_FILES['options_1_file'] = [
            'name' => 'test.jpg',
            'type' => 'image/jpeg',
            'tmp_name' => $filePath,
        ];
    }

    /**
     * @return array
     */
    protected function expectedValidate()
    {
        return [
            'type' => 'image/jpeg',
            'title' => 'test.jpg',
            'quote_path' => 'custom_options/quote/t/e/a071b9ffc8fda6df1652c05a4c61bf8a.jpg',
            'order_path' => 'custom_options/order/t/e/a071b9ffc8fda6df1652c05a4c61bf8a.jpg',
            'size' => '3046',
            'width' => 136,
            'height' => 131,
        ];
    }
}
