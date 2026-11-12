<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryCatalogUi\Controller\Adminhtml\Product;

use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager as FixtureManager;
use Magento\TestFramework\TestCase\AbstractBackendController;

#[AppArea('adminhtml')]
class GetSelectedTest extends AbstractBackendController
{
    #[DataFixture(
        ProductFixture::class,
        ['name' => 'Simple Product', 'sku' => 'simple', 'status' => Status::STATUS_ENABLED],
        'product'
    )]
    public function testExecuteReturnsProductStatus(): void
    {
        $product = FixtureManager::getStorage()->get('product');
        $this->getRequest()->setMethod(HttpRequest::METHOD_GET)->setParams(['ids' => [$product->getId()]]);

        $this->dispatch('backend/media_gallery_catalog/product/getSelected');

        $response = json_decode($this->getResponse()->getBody(), true);
        self::assertCount(1, $response);
        self::assertSame((string) $product->getId(), $response[0]['value']);
        self::assertSame($product->getName(), $response[0]['label']);
        self::assertSame((string) Status::STATUS_ENABLED, $response[0]['is_active']);
        self::assertSame($product->getSku(), $response[0]['path']);
    }
}
