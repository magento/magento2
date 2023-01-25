<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Product\Option\Type\File;

use Laminas\Validator\File\ExcludeExtension;
use Laminas\Validator\File\FilesSize;
use Laminas\Validator\File\ImageSize;
use Magento\Framework\File\Http;
use Magento\Framework\Math\Random;

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
     * @var \Magento\Framework\HTTP\Adapter\FileTransferFactory|\PHPUnit\Framework\MockObject\MockObject
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

    protected function setUp(): void
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
        $random = $this->getMockBuilder(Random::class)
            ->disableOriginalConstructor()
            ->getMock();
        $random->expects($this->any())
            ->method('getRandomString')
            ->willReturn('RandomString');

        $this->model = $this->objectManager->create(
            ValidatorFile::class,
            [
                'httpFactory' => $this->httpFactoryMock,
                'random' => $random,
            ]
        );
    }

    /**
     * @return void
     */
    public function testRunValidationException()
    {
        $this->expectException(\Magento\Framework\Validator\Exception::class);

        $httpAdapterMock = $this->createPartialMock(Http::class, ['isValid']);
        $this->httpFactoryMock->expects($this->once())->method('create')->willReturn($httpAdapterMock);

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
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        $exceptionMessage = 'The file was too big and couldn\'t be uploaded. Use a file smaller than %s MBs and try ' .
            'to upload again.';
        $this->expectExceptionMessage(
            sprintf($exceptionMessage, $this->maxFileSizeInMb)
        );
        $this->prepareEnv();
        $_SERVER['CONTENT_LENGTH'] = $this->maxFileSize + 1;
        $httpAdapterMock = $this->createPartialMock(Http::class, ['getFileInfo']);
        $exception = function () {
            throw new \Exception();
        };
        $httpAdapterMock->expects($this->once())->method('getFileInfo')->willReturnCallback($exception);
        $this->httpFactoryMock->expects($this->once())->method('create')->willReturn($httpAdapterMock);

        $property = new \ReflectionProperty($httpAdapterMock, 'files');
        $property->setAccessible(true);
        $property->setValue($httpAdapterMock, ['options_1_file' => $_FILES['options_1_file']]);
        $this->model->validate(
            $this->objectManager->create(\Magento\Framework\DataObject::class),
            $this->getProductOption(['is_require' => false])
        );
    }

    /**
     * @return void
     */
    public function testOptionRequiredException()
    {
        $this->expectException(\Magento\Catalog\Model\Product\Exception::class);

        $this->prepareEnv();
        $httpAdapterMock = $this->createPartialMock(Http::class, ['getFileInfo']);
        $exception = function () {
            throw new \Exception();
        };
        $httpAdapterMock->expects($this->once())->method('getFileInfo')->willReturnCallback($exception);
        $this->httpFactoryMock->expects($this->once())->method('create')->willReturn($httpAdapterMock);

        $property = new \ReflectionProperty($httpAdapterMock, 'files');
        $property->setAccessible(true);
        $property->setValue($httpAdapterMock, ['options_1_file' => $_FILES['options_1_file']]);
        $this->model->validate(
            $this->objectManager->create(\Magento\Framework\DataObject::class),
            $this->getProductOption(['is_require' => false])
        );
    }

    /**
     * @return void
     */
    public function testException()
    {
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);

        $this->prepareEnv();
        $httpAdapterMock = $this->createPartialMock(Http::class, ['isUploaded']);
        $httpAdapterMock->expects($this->once())->method('isUploaded')->willReturn(false);
        $this->httpFactoryMock->expects($this->once())->method('create')->willReturn($httpAdapterMock);

        $property = new \ReflectionProperty($httpAdapterMock, 'files');
        $property->setAccessible(true);
        $property->setValue($httpAdapterMock, ['options_1_file' => $_FILES['options_1_file']]);
        $this->model->validate(
            $this->objectManager->create(\Magento\Framework\DataObject::class),
            $this->getProductOption()
        );

        $this->expectExceptionMessage(
            "The product's required option(s) weren't entered. Make sure the options are entered and try again."
        );
    }

    /**
     * @return void
     */
    public function testInvalidateFile()
    {
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        $this->expectExceptionMessage(
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
            Http::class,
            ['isValid', 'getErrors', 'getFileInfo', 'isUploaded']
        );
        $httpAdapterMock->expects($this->once())
            ->method('getFileInfo')
            ->willReturn([
                'options_1_file' => [
                    'name' => 'test.jpg'
                ]
            ]);
        $httpAdapterMock->expects($this->once())
            ->method('isValid')
            ->willReturn(false);
        $httpAdapterMock->expects($this->exactly(2))
            ->method('getErrors')
            ->willReturn(
                [
                    ExcludeExtension::FALSE_EXTENSION,
                    ExcludeExtension::FALSE_EXTENSION,
                    ImageSize::WIDTH_TOO_BIG,
                    FilesSize::TOO_BIG,
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
        $httpAdapterMock = $this->createPartialMock(Http::class, ['isValid']);
        $httpAdapterMock->expects($this->once())->method('isValid')->willReturn(true);
        $this->httpFactoryMock->expects($this->once())->method('create')->willReturn($httpAdapterMock);

        $property = new \ReflectionProperty($httpAdapterMock, 'files');
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

        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        $this->expectExceptionMessage('The file is empty. Select another file and try again.');

        $httpAdapterMock = $this->createPartialMock(Http::class, ['isValid']);
        $httpAdapterMock->expects($this->once())->method('isValid')->willReturn(true);
        $this->httpFactoryMock->expects($this->once())->method('create')->willReturn($httpAdapterMock);

        $property = new \ReflectionProperty($httpAdapterMock, 'files');
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
            'quote_path' => 'custom_options/quote/R/a/RandomString',
            'order_path' => 'custom_options/order/R/a/RandomString',
            'size' => '3046',
            'width' => 136,
            'height' => 131,
        ];
    }
}
