<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Product\Option\Type\File;

use Magento\Catalog\Model\Product\Media\Config;
use Magento\Catalog\Model\Product\Option;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\File\Size;
use Magento\Framework\Filesystem;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Zend_Validate;
use Zend_Validate_File_ExcludeExtension;
use Zend_Validate_File_Extension;
use Zend_Validate_File_FilesSize;
use Zend_Validate_File_ImageSize;

/**

 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ValidatorInfoTest extends TestCase
{
    /**
     * @var ValidatorInfo
     */
    protected $model;

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /** @var int */
    protected $maxFileSizeInMb;

    /**
     * @var ValidateFactory|MockObject
     */
    protected $validateFactoryMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        /** @var Size $fileSize */
        $fileSize = $this->objectManager->create(Size::class);
        $this->maxFileSizeInMb = $fileSize->getMaxFileSizeInMb();

        $this->validateFactoryMock = $this->createPartialMock(ValidateFactory::class, ['create']);
        $this->model = $this->objectManager->create(
            ValidatorInfo::class,
            [
                'validateFactory' => $this->validateFactoryMock,
            ]
        );
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/validate_image_info.php
     * @return void
     */
    public function testExceptionWithErrors(): void
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage(
            "The file 'test.jpg' for 'MediaOption' has an invalid extension.\n"
            . "The file 'test.jpg' for 'MediaOption' has an invalid extension.\n"
            . "The maximum allowed image size for 'MediaOption' is 2000x2000 px.\n"
            . sprintf(
                "The file 'test.jpg' you uploaded is larger than the %s megabytes allowed by our server.",
                $this->maxFileSizeInMb
            )
        );

        $validateMock = $this->createPartialMock(Zend_Validate::class, ['isValid', 'getErrors']);
        $validateMock->expects($this->once())->method('isValid')->willReturn(false);
        $validateMock->expects($this->exactly(1))->method('getErrors')->willReturn([
            Zend_Validate_File_ExcludeExtension::FALSE_EXTENSION,
            Zend_Validate_File_Extension::FALSE_EXTENSION,
            Zend_Validate_File_ImageSize::WIDTH_TOO_BIG,
            Zend_Validate_File_FilesSize::TOO_BIG,
        ]);
        $this->validateFactoryMock->expects($this->once())->method('create')->willReturn($validateMock);

        $this->model->validate($this->getOptionValue(), $this->getProductOption());
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/validate_image_info.php
     * @return void
     */
    public function testExceptionWithoutErrors(): void
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage(
            "The product's required option(s) weren't entered. Make sure the options are entered and try again."
        );

        $validateMock = $this->createPartialMock(Zend_Validate::class, ['isValid', 'getErrors']);
        $validateMock->expects($this->once())->method('isValid')->willReturn(false);
        $validateMock->expects($this->exactly(1))->method('getErrors')->willReturn([]);
        $this->validateFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($validateMock);

        $this->model->validate($this->getOptionValue(), $this->getProductOption());
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/validate_image_info_another.php
     * @return void
     */
    public function testExceptionWrongExtension(): void
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage(
            "The product's required option(s) weren't entered. Make sure the options are entered and try again."
        );

        $validateMock = $this->createPartialMock(Zend_Validate::class, ['isValid', 'getErrors']);
        $validateMock->expects($this->exactly(1))->method('getErrors')->willReturn([]);
        $this->validateFactoryMock->expects($this->once())->method('create')->willReturn($validateMock);

        $this->model->validate($this->getOptionValue('magento_small_image.svg'), $this->getProductOption());
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/validate_image_info.php
     * @return void
     */
    public function testValidate(): void
    {
        //use actual zend class to test changed functionality
        $validate = $this->objectManager->create(Zend_Validate::class);
        $this->validateFactoryMock->expects($this->once())->method('create')->willReturn($validate);

        $result = $this->model->validate($this->getOptionValue(), $this->getProductOption());
        $this->assertTrue($result);
    }

    /**
     * @param array $options
     * @return Option
     */
    protected function getProductOption(array $options = []): Option
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

        return $this->objectManager->create(
            Option::class,
            [
                'data' => array_merge($data, $options)
            ]
        );
    }

    /**
     * @param string|null $fileName
     * @return array
     */
    protected function getOptionValue(?string $fileName = 'magento_small_image.jpg'): array
    {
        /** @var Config $config */
        $config = $this->objectManager->get(Config::class);
        $file = $config->getBaseTmpMediaPath() . '/' . $fileName;

        /** @var Filesystem $filesystem */
        $filesystem = $this->objectManager->get(Filesystem::class);
        $tmpDirectory = $filesystem->getDirectoryRead(DirectoryList::MEDIA);
        $filePath = $tmpDirectory->getAbsolutePath($file);

        return [
            'title' => 'test.jpg',
            'quote_path' => $file,
            'order_path' => $file,
            // phpcs:ignore Magento2.Security.InsecureFunction
            'secret_key' => substr(md5(file_get_contents($filePath)), 0, 20),
        ];
    }
}
