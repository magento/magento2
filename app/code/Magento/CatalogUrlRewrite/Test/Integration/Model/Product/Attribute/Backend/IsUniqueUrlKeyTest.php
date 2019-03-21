<?php

namespace Magento\Catalog\Test\Integration\Model\Product\Attribute\Backend;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\Exception\LocalizedException;
use PHPUnit\Framework\TestCase;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as CollectionProduct;
use Magento\Framework\Registry;

/**
 * Class IsUniqueUrlKeyTest
 * @package Magento\Catalog\Test\Integration\Model\Product\Attribute\Backend
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class IsUniqueUrlKeyTest extends TestCase
{
    /**
     * @var ProductRepositoryInterface $productRepository
     */
    private $productRepository;

    /**
     * @var CollectionProduct $productCollection
     */
    private $productCollection;

    /**
     * @var ProductResource $productResource
     */
    private $productResource;

    /**
     * @var Registry $registry
     */
    private $registry;

    public function setUp()
    {
        $this->productRepository = Bootstrap::getObjectManager()->create(ProductRepositoryInterface::class);
        $this->productCollection = Bootstrap::getObjectManager()->get(CollectionProduct::class);
        $this->productResource = Bootstrap::getObjectManager()->get(ProductResource::class);
        $this->registry = Bootstrap::getObjectManager()->get(Registry::class);
    }

    /**
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\StateException
     * @magentoAppArea adminhtml
     */
    public function testShouldRaiseExceptionUrlKeyIsAlreadytaken()
    {
        /** @var $product \Magento\Catalog\Model\Product */
        $product = Bootstrap::getObjectManager()->create(\Magento\Catalog\Model\Product::class);
        $product->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE)
            ->setAttributeSetId(4)
            ->setWebsiteIds([1])
            ->setName('Simple Product')
            ->setSku('simple')
            ->setPrice(10)
            ->setDescription('Description with <b>html tag</b>')
            ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
            ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
            ->setCategoryIds([2])
            ->setStockData(['use_config_manage_stock' => 1, 'qty' => 100, 'is_qty_decimal' => 0, 'is_in_stock' => 1])
            ->setUrlKey('url-key');
        $this->productRepository->save($product);

        /** @var $product \Magento\Catalog\Model\Product */
        $product = Bootstrap::getObjectManager()->create(\Magento\Catalog\Model\Product::class);
        $product->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE)
            ->setAttributeSetId(4)
            ->setWebsiteIds([1])
            ->setName('Simple Product 2')
            ->setSku('simple2')
            ->setPrice(10)
            ->setDescription('Description with <b>html tag</b>')
            ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE)
            ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
            ->setCategoryIds([2])
            ->setStockData(['use_config_manage_stock' => 1, 'qty' => 100, 'is_qty_decimal' => 0, 'is_in_stock' => 1])
            ->setUrlKey('url-key2');
        $this->productRepository->save($product);
        $this->expectException(LocalizedException::class);
        $product = $this->productRepository->get('simple2');
        $product->setUrlKey('url-key');
        $this->productRepository->save($product);
    }

    public function tearDown()
    {
        $this->registry->register('isSecureArea', true);
        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $prodCollection */
        $prodCollection = $this->productCollection->create();
        foreach ($prodCollection as $product) {
            $this->productResource->delete($product);
        }
        $this->registry->unregister('isSecureArea');
    }
}
