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
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

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

    /**
     * @var Filesystem\Directory\WriteInterface
     */
    private $mediaDirectory;

    /**
     * @var Config
     */
    private $config;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $filesystem = $this->objectManager->get(Filesystem::class);
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->config = $this->objectManager->get(Config::class);
        $this->model = $this->objectManager->create(ValidatorInfo::class);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/validate_image_info.php
     * @return void
     */
    public function testExceptionWithSizeErrors(): void
    {
        $maxSize = 2 * 1024;
        /** @var Size|MockObject $fileSizeMock */
        $fileSizeMock = $this->createPartialMock(Size::class, ['getMaxFileSize']);
        $fileSizeMock->expects(self::any())
            ->method('getMaxFileSize')
            ->willReturn($maxSize);

        $modelMock = $this->objectManager->create(ValidatorInfo::class, ['fileSize' => $fileSizeMock]);

        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage(
            "The maximum allowed image size for 'MediaOption' is 100x200 px.\n" .
            "The file 'test.jpg' you uploaded is larger than the 0 megabytes allowed by our server."
        );

        $modelMock->validate($this->getOptionValue(), $this->getProductOption([
            'image_size_x' => '100',
            'image_size_y' => '200',
        ]));
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/validate_image_info.php
     * @return void
     */
    public function testExceptionWrongExtension(): void
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage(
            "The file 'test.jpg' for 'MediaOption' has an invalid extension."
        );

        $this->model->validate($this->getOptionValue(), $this->getProductOption([
            'file_extension' => 'png'
        ]));
    }

    /**
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function testFileNotImage(): void
    {
        $this->copyFileToTmpMedia(INTEGRATION_TESTS_DIR . '/testsuite/Magento/Catalog/_files/empty.csv');

        self::assertFalse($this->model->validate($this->getOptionValue('empty.csv'), $this->getProductOption()));
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/validate_image_info_another.php
     * @return void
     */
    public function testExceptionNotAllowedExtension(): void
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage(
            "The product's required option(s) weren't entered. Make sure the options are entered and try again."
        );

        $this->model->validate($this->getOptionValue('magento_small_image.svg'), $this->getProductOption());
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/validate_image_info.php
     * @return void
     */
    public function testExceptionWithoutErrors(): void
    {
        $result = $this->model->validate($this->getOptionValue(), $this->getProductOption());
        self::assertTrue($result);
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
    private function getOptionValue(?string $fileName = 'magento_small_image.jpg'): array
    {
        $file = $this->config->getBaseTmpMediaPath() . '/' . $fileName;
        $filePath = $this->mediaDirectory->getAbsolutePath($file);

        return [
            'title' => 'test.jpg',
            'quote_path' => $file,
            'order_path' => $file,
            'secret_key' => substr(hash('sha256', $this->mediaDirectory->readFile($filePath)), 0, 20)
        ];
    }

    /**
     * @param string $source
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    private function copyFileToTmpMedia(string $source): void
    {
        $destination = $this->mediaDirectory->getAbsolutePath(
            $this->config->getBaseTmpMediaPath() . DIRECTORY_SEPARATOR . basename($source)
        );

        $driver = $this->mediaDirectory->getDriver();
        $driver->createDirectory(dirname($destination));
        $driver->filePutContents($destination, file_get_contents($source));
    }
}
