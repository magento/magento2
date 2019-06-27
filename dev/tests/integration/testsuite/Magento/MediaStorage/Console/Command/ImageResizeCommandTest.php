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
 *
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
     * @inheritdoc
     */
    public function setUp()
    {
        $this->fileName = 'image.jpg';
        $this->objectManager = Bootstrap::getObjectManager();
        $this->command = $this->objectManager->get(ImagesResizeCommand::class);
        $this->tester = new CommandTester($this->command);
        $this->filesystem = $this->objectManager->get(Filesystem::class);
        $this->mediaDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
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
    }

    /**
     * @inheritDoc
     */
    public function tearDown()
    {
        $this->mediaDirectory->getDriver()->deleteFile($this->mediaDirectory->getAbsolutePath($this->fileName));
    }
}
