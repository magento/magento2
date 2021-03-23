<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Controller\Adminhtml\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\MessageInterface;
use Magento\TestFramework\TestCase\AbstractBackendController;

/**
 * Test class for Product duplicate action
 *
 * @magentoAppArea adminhtml
 * @see \Magento\Catalog\Controller\Adminhtml\Product\Duplicate
 */
class DuplicateTest extends AbstractBackendController
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var string
     */
    private $duplicatedProductSku;

    /**
     * @var array
     */
    private $dataKeys = ['name', 'description', 'short_description', 'price', 'weight', 'attribute_set_id'];

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->productRepository = $this->_objectManager->get(ProductRepositoryInterface::class);
        $this->productRepository->cleanCache();
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        try {
            $this->productRepository->deleteById($this->duplicatedProductSku);
        } catch (NoSuchEntityException $e) {
            // product already deleted
        }

        parent::tearDown();
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     *
     * @return void
     */
    public function testDuplicateAction(): void
    {
        $product = $this->productRepository->get('simple');
        $this->getRequest()->setMethod(HttpRequest::METHOD_GET);
        $this->getRequest()->setParams(
            [
                'id' => $product->getId(),
                'attribute_set_id' => $product->getAttributeSetId(),
            ]
        );
        $this->dispatch('backend/catalog/product/duplicate');
        $this->assertSessionMessages(
            $this->containsEqual((string)__('You duplicated the product.')),
            MessageInterface::TYPE_SUCCESS
        );
        $this->assertRedirect($this->stringContains('catalog/product/edit/'));
        $productId = $this->getIdFromRedirectedUrl();
        $this->assertNotEmpty($productId, 'Id not found');
        $duplicatedProduct = $this->productRepository->getById((int)$productId);
        $this->duplicatedProductSku = $duplicatedProduct->getSku();
        $this->assertProductDuplicated($product, $duplicatedProduct);
    }

    /**
     * Get id value from redirected url
     *
     * @return string
     */
    private function getIdFromRedirectedUrl(): string
    {
        $url = $this->getResponse()
            ->getHeader('Location')
            ->getFieldValue();
        $pattern = '!/id/(.*?)/!';
        $result = preg_match($pattern, $url, $matches);

        return $result ? $matches[1] : '';
    }

    /**
     * Checks that duplicate product was created from the first product
     *
     * @param ProductInterface $product
     * @param ProductInterface $duplicatedProduct
     * @return void
     */
    private function assertProductDuplicated(ProductInterface $product, ProductInterface $duplicatedProduct): void
    {
        foreach ($this->dataKeys as $key) {
            $this->assertEquals($product->getData($key), $duplicatedProduct->getData($key));
        }
    }
}
