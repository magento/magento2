<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Category\Link;

use Magento\Catalog\Api\Data\CategoryLinkInterfaceFactory;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Save handler test
 *
 * @magentoDataFixture Magento/Catalog/_files/categories_no_products.php
 * @magentoDataFixture Magento/Catalog/_files/second_product_simple.php
 */
class SaveHandlerTest extends TestCase
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var string
     */
    private $productLinkField;

    /**
     * @var CategoryLinkInterfaceFactory
     */
    private $categoryLinkFactory;

    /**
     * @var SaveHandler
     */
    private $saveHandler;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->productRepository = $objectManager->create(ProductRepositoryInterface::class);
        $metadataPool = $objectManager->create(MetadataPool::class);
        $this->productLinkField = $metadataPool->getMetadata(ProductInterface::class)
            ->getLinkField();
        $this->categoryLinkFactory = $objectManager->create(CategoryLinkInterfaceFactory::class);
        $this->saveHandler = $objectManager->create(SaveHandler::class);
    }

    /**
     * Execute test
     *
     * @return void
     */
    public function testExecute(): void
    {
        $product = $this->productRepository->get('simple2');
        $product->setCategoryIds([3, 4, 6]);
        $this->productRepository->save($product);
        $categoryPositions = [
            3 => [
                'category_id' => 3,
                'position' => 0,
            ],
            4 => [
                'category_id' => 4,
                'position' => 0,
            ],
            6 => [
                'category_id' => 6,
                'position' => 0,
            ],
        ];

        $categoryLinks = $product->getExtensionAttributes()->getCategoryLinks();
        $this->assertEmpty($categoryLinks);

        $categoryLinks = [];
        $categoryPositions[4]['position'] = 1;
        $categoryPositions[6]['position'] = 1;
        foreach ($categoryPositions as $categoryPosition) {
            $categoryLink = $this->categoryLinkFactory->create()
                ->setCategoryId($categoryPosition['category_id'])
                ->setPosition($categoryPosition['position']);
            $categoryLinks[] = $categoryLink;
        }
        $categoryLinks = $this->updateCategoryLinks($product, $categoryLinks);
        $this->assertPositions($categoryPositions, $categoryLinks);

        $categoryPositions[4]['position'] = 2;
        $categoryLink = $this->categoryLinkFactory->create()
            ->setCategoryId(4)
            ->setPosition($categoryPositions[4]['position']);
        $categoryLinks = $this->updateCategoryLinks($product, [$categoryLink]);
        $this->assertPositions($categoryPositions, $categoryLinks);
    }

    /**
     * Update category links
     *
     * @param ProductInterface $product
     * @param \Magento\Catalog\Api\Data\CategoryLinkInterface[] $categoryLinks
     * @return \Magento\Catalog\Api\Data\CategoryLinkInterface[]
     */
    private function updateCategoryLinks(ProductInterface $product, array $categoryLinks): array
    {
        $product->getExtensionAttributes()->setCategoryLinks($categoryLinks);
        $arguments = [$this->productLinkField => $product->getData($this->productLinkField)];
        $this->saveHandler->execute($product, $arguments);
        $product = $this->productRepository->get($product->getSku(), false, null, true);
        $categoryLinks = $product->getExtensionAttributes()
            ->getCategoryLinks();

        return $categoryLinks;
    }

    /**
     * Assert positions
     *
     * @param array $categoryPositions
     * @param array $categoryLinks
     * @return void
     */
    private function assertPositions(array $categoryPositions, array $categoryLinks): void
    {
        foreach ($categoryLinks as $categoryLink) {
            $categoryPosition = $categoryPositions[$categoryLink->getCategoryId()];
            $this->assertEquals($categoryPosition['category_id'], $categoryLink->getCategoryId());
            $this->assertEquals($categoryPosition['position'], $categoryLink->getPosition());
        }
    }
}
