<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaStorage\Console\Command;

use Magento\Catalog\Model\Product\Gallery\Processor;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Test for \Magento\MediaStorage\Console\Command\ImagesResizeCommand.
 */
class ImageResizeCommandTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CommandTester
     */
    private $tester;

    /**
     * @var ImagesResizeCommand
     */
    private $command;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

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
     * @inheritDoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->fileName = 'image.jpg';
        $this->command = $this->objectManager->get(ImagesResizeCommand::class);
        $this->tester = new CommandTester($this->command);
        $this->filesystem = $this->objectManager->get(Filesystem::class);
        $this->mediaDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
    }

    /**
     * Test that catalog:image:resize command executed successfully with missing image file
     *
     * @magentoDataFixture Magento/MediaStorage/_files/product_with_missed_image.php
     */
    public function testRunResizeWithMissingFile()
    {
        $this->tester->execute([]);
        $this->assertContains('original image not found', $this->tester->getDisplay());
    }

    /**
     * Test command with zero byte file
     *
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDataFixture Magento/Catalog/_files/product_image.php
     *
     * @return void
     */
    public function testExecuteWithZeroByteImage()
    {
        $this->mediaDirectory->writeFile($this->fileName, '');

        /** @var ProductRepository $productRepository */
        $productRepository = $this->objectManager->create(ProductRepository::class);
        $product = $productRepository->getById(1);

        /** @var Processor $mediaGalleryProcessor */
        $mediaGalleryProcessor = $this->objectManager->get(Processor::class);
        $mediaGalleryProcessor->addImage(
            $product,
            $this->mediaDirectory->getAbsolutePath($this->fileName),
            ['image','thumbnail','small_image'],
            false,
            false
        );

        $product->save();

        $this->tester->execute([]);
        $this->assertContains('Wrong file', $this->tester->getDisplay());
        $this->mediaDirectory->getDriver()->deleteFile($this->mediaDirectory->getAbsolutePath($this->fileName));
    }

    /**
     * Test that catalog:image:resize command executes successfully in database storage mode
     * with file missing from local folder
     *
     * @magentoDataFixture Magento/MediaStorage/_files/database_mode.php
     * @magentoDataFixture Magento/MediaStorage/_files/product_with_missed_image.php
     */
    public function testDatabaseStorageMissingFile()
    {
        $this->tester->execute([]);
        $this->assertContains('Product images resized successfully', $this->tester->getDisplay());
    }
}
