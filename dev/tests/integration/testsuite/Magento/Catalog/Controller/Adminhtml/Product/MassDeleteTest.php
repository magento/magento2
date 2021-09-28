<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Controller\Adminhtml\Product;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\ObjectManagerInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\Store;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Framework\Message\MessageInterface;
use Magento\TestFramework\TestCase\AbstractBackendController;
use Magento\Catalog\Model\ResourceModel\Product\Gallery;

/**
 * Test for mass product deleting.
 *
 * @see \Magento\Catalog\Controller\Adminhtml\Product\MassDelete
 * @magentoAppArea adminhtml
 * @magentoDbIsolation enabled
 */
class MassDeleteTest extends AbstractBackendController
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /** @var ProductRepositoryInterface */
    protected $productRepository;

    /**
     * @var ProductResource
     */
    private $productResource;

    /**
     * @var Gallery
     */
    private $galleryResource;

    /**
     * @var int
     */
    private $mediaAttributeId;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->productRepository = $this->_objectManager->get(ProductRepositoryInterface::class);
        $this->productResource = $this->objectManager->create(ProductResource::class);
        $this->galleryResource = $this->objectManager->create(Gallery::class);
        $this->mediaAttributeId = (int)$this->productResource->getAttribute('media_gallery')->getAttributeId();
        $this->productRepository->cleanCache();
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/multiple_products.php
     *
     * @return void
     */
    public function testDeleteSimpleProductViaMassAction(): void
    {
        $productIds = [10, 11, 12];
        $this->dispatchMassDeleteAction($productIds);
        $this->assertSuccessfulDeleteProducts(count($productIds));
    }

    /**
     * Tests image remove during product delete.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_with_media_gallery.php
     *
     * @return void
     * @throws NoSuchEntityException
     */
    public function testDeleteSimpleProductWithImageViaMassAction(): void
    {
        $productIds = [812];
        $product = $this->productRepository->get(
            'simple_product_with_media',
            false,
            Store::DEFAULT_STORE_ID,
            true
        );
        $this->dispatchMassDeleteAction($productIds);
        $this->assertSuccessfulDeleteProducts(count($productIds));
        $productImages = $this->galleryResource->loadProductGalleryByAttributeId($product, $this->mediaAttributeId);
        $this->assertCount(0, $productImages);
    }

    /**
     * @return void
     */
    public function testDeleteNotExistingProductViaMassAction(): void
    {
        $this->dispatchMassDeleteAction([989]);
        $this->assertSessionMessages($this->isEmpty(), MessageInterface::TYPE_ERROR);
        $this->assertRedirect($this->stringContains('backend/catalog/product/index'));
    }

    /**
     * @return void
     */
    public function testMassDeleteWithoutProductIds(): void
    {
        $this->markTestSkipped('Test is blocked by issue MC-34495');
        $this->dispatchMassDeleteAction();
        $this->assertSessionMessages(
            $this->equalTo('An item needs to be selected. Select and try again.'),
            MessageInterface::TYPE_ERROR
        );
        $this->assertRedirect($this->stringContains('backend/catalog/product/index'));
    }

    /**
     * Assert successful delete products.
     *
     * @param int $productCount
     * @return void
     */
    protected function assertSuccessfulDeleteProducts(int $productCount): void
    {
        $this->assertSessionMessages(
            $this->equalTo([(string)__('A total of %1 record(s) have been deleted.', $productCount)]),
            MessageInterface::TYPE_SUCCESS
        );
        $this->assertRedirect($this->stringContains('backend/catalog/product/index'));
    }

    /**
     * Dispatch mass delete action.
     *
     * @param array $productIds
     * @return void
     */
    protected function dispatchMassDeleteAction(array $productIds = []): void
    {
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setParams(['selected' => $productIds, 'namespace' => 'product_listing']);
        $this->dispatch('backend/catalog/product/massDelete/');
    }

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        $reflection = new \ReflectionObject($this);
        foreach ($reflection->getProperties() as $property) {
            if (!$property->isStatic() && 0 !== strpos($property->getDeclaringClass()->getName(), 'PHPUnit')) {
                $property->setAccessible(true);
                $property->setValue($this, null);
            }
        }
    }
}
