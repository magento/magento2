<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Catalog\Model\Product\Gallery;

use Magento\Catalog\Model\Product;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\Filesystem\Directory\WriteInterface;

/**
 * Test for \Magento\Catalog\Model\Product\Gallery\UpdateHandler.
 *
 * @magentoDataFixture Magento/Catalog/_files/product_simple.php
 * @magentoDataFixture Magento/Catalog/_files/product_image.php
 */
class UpdateHandlerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var UpdateHandler
     */
    private $updateHandler;

    /**
     * @var WriteInterface
     */
    private $mediaDirectory;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var string
     */
    private $fileName;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->fileName = 'image.txt';

        $this->objectManager = Bootstrap::getObjectManager();
        $this->updateHandler = $this->objectManager->create(UpdateHandler::class);
        $this->filesystem = $this->objectManager->get(Filesystem::class);
        $this->mediaDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->mediaDirectory->writeFile($this->fileName, 'Test');
    }

    /**
     * @return void
     */
    public function testExecuteWithIllegalFilename(): void
    {
        $filePath = str_repeat('/..', 2) . DIRECTORY_SEPARATOR . $this->fileName;

        /** @var $product Product */
        $product = Bootstrap::getObjectManager()->create(Product::class);
        $product->load(1);
        $product->setData(
            'media_gallery',
            [
                'images' => [
                    'image' => [
                        'value_id' => '100',
                        'file' => $filePath,
                        'label' => 'New image',
                        'removed' => 1,
                    ],
                ],
            ]
        );

        $this->updateHandler->execute($product);
        $this->assertFileExists($this->mediaDirectory->getAbsolutePath($this->fileName));
    }

    /**
     * @return void
     */
    protected function tearDown(): void
    {
        $this->mediaDirectory->getDriver()->deleteFile($this->mediaDirectory->getAbsolutePath($this->fileName));
    }
}
