<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\ObjectManagerInterface;

/**
 * Test removing old Category Image file from pub/media/catalog/category directory if such Image is not used anymore.
 */
class RemoveRedundantImageTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var WriteInterface
     */
    private $mediaDirectory;

    /**
     * @var CategoryRepository
     */
    private $categoryRepository;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var Filesystem $filesystem */
        $this->filesystem = $this->objectManager->get(Filesystem::class);
        $this->mediaDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->categoryRepository = $this->objectManager->get(CategoryRepository::class);
    }

    /**
     * Tests removing Image file if it is not used anymore.
     *
     * @magentoDataFixture Magento/Catalog/_files/categories_with_image.php
     *
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     */
    public function testRemoveRedundantImage()
    {
        $imagesPath = 'catalog' . DIRECTORY_SEPARATOR . 'category';
        $absoluteImagesPath = $this->mediaDirectory->getAbsolutePath($imagesPath);
        $filePath1 = $absoluteImagesPath . DIRECTORY_SEPARATOR . 'test_image_1.jpg';
        $filePath2 = $absoluteImagesPath . DIRECTORY_SEPARATOR . 'test_image_2.jpg';
        $this->mediaDirectory->create($absoluteImagesPath);
        $this->mediaDirectory->touch($filePath1);
        $this->mediaDirectory->touch($filePath2);

        $category1 = $this->categoryRepository->get(3);
        $category1->setImage('test_image_3.jpg');
        $this->categoryRepository->save($category1);
        $category2 = $this->categoryRepository->get(5);
        $category2->setImage('test_image_3.jpg');
        $this->categoryRepository->save($category2);

        $this->assertTrue($this->mediaDirectory->isExist($filePath1));
        $this->assertFalse($this->mediaDirectory->isExist($filePath2));
    }

    protected function tearDown()
    {
        $this->mediaDirectory->delete();
    }
}
