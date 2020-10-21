<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Catalog\Model\ResourceModel;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Category as CategoryModel;
use Magento\Catalog\Model\ResourceModel\Category as CategoryResource;
use Magento\Catalog\Model\ResourceModel\Category\Collection as CategoryCollection;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Tests category resource model
 *
 * @see \Magento\Catalog\Model\ResourceModel\Category
 */
class CategoryTest extends TestCase
{
    private const BASE_TMP_PATH = 'catalog/tmp/category';

    private const BASE_PATH = 'catalog/category';

    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var CategoryRepositoryInterface */
    private $categoryRepository;

    /** @var CategoryResource */
    private $categoryResource;

    /** @var StoreManagerInterface */
    private $storeManager;

    /** @var CategoryCollection */
    private $categoryCollection;

    /** @var Filesystem */
    private $filesystem;

    /** @var WriteInterface */
    private $mediaDirectory;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->categoryRepository = $this->objectManager->get(CategoryRepositoryInterface::class);
        $this->categoryResource = $this->objectManager->get(CategoryResource::class);
        $this->storeManager = $this->objectManager->get(StoreManagerInterface::class);
        $this->categoryCollection = $this->objectManager->get(CategoryCollectionFactory::class)->create();
        $this->filesystem = $this->objectManager->get(Filesystem::class);
        $this->mediaDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->mediaDirectory->delete(self::BASE_PATH);

        parent::tearDown();
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/category.php
     * @magentoDataFixture Magento/Catalog/_files/catalog_tmp_category_image.php
     * @magentoDbIsolation disabled
     * @return void
     */
    public function testAddImageForCategory(): void
    {
        $dataImage = [
            'name' => 'magento_small_image.jpg',
            'type' => 'image/jpg',
            'tmp_name' => '/tmp/phpDstnAx',
            'file' => 'magento_small_image.jpg',
            'url' => $this->prepareDataImageUrl('magento_small_image.jpg'),
        ];
        $imageRelativePath = self::BASE_PATH . DIRECTORY_SEPARATOR . $dataImage['file'];
        $expectedImage = DIRECTORY_SEPARATOR . $this->storeManager->getStore()->getBaseMediaDir()
            . DIRECTORY_SEPARATOR . $imageRelativePath;
        /** @var CategoryModel $category */
        $category = $this->categoryRepository->get(333);
        $category->setImage([$dataImage]);

        $this->categoryResource->save($category);

        $categoryModel = $this->categoryCollection
            ->addAttributeToSelect('image')
            ->addIdFilter([$category->getId()])
            ->getFirstItem();
        $this->assertEquals(
            $expectedImage,
            $categoryModel->getImage(),
            'The path of the expected image does not match the path to the actual image.'
        );
        $this->assertFileExists($this->mediaDirectory->getAbsolutePath($imageRelativePath));
    }

    /**
     * Prepare image url for image data
     *
     * @param string $file
     * @return string
     */
    private function prepareDataImageUrl(string $file): string
    {
        return $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA)
            . self::BASE_TMP_PATH . DIRECTORY_SEPARATOR . $file;
    }
}
