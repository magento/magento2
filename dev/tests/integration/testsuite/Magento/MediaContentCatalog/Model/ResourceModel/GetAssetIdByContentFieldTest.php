<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 *
 */
declare(strict_types=1);

namespace Magento\MediaContentCatalog\Model\ResourceModel;

use Magento\MediaContentApi\Api\GetAssetIdByContentFieldInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test for GetAssetIdByContentFieldTest
 */
class GetAssetIdByContentFieldTest extends TestCase
{
    private const STORE_FIELD = 'store_id';
    private const STATUS_FIELD = 'content_status';
    private const STATUS_ENABLED = '1';
    private const STATUS_DISABLED = '0';

    /**
     * @var GetAssetIdByContentFieldInterface
     */
    private $getAssetIdByContentField;

    /**
     * @var int
     */
    private $storeId;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->storeId = $objectManager->get(StoreManagerInterface::class)->getStore()->getId();
        $this->getAssetIdByContentField = $objectManager->get(GetAssetIdByContentFieldInterface::class);
    }

    /**
     * Test for getting asset id by store view of a category
     *
     * @magentoDataFixture Magento/MediaGallery/_files/media_asset.php
     * @magentoDataFixture Magento/MediaContentCatalog/_files/category_with_asset.php
     */
    public function testCategoryStoreView(): void
    {
        $this->assertEquals(
            [2020],
            $this->getAssetIdByContentField->execute(self::STORE_FIELD, (string)$this->storeId)
        );
    }

    /**
     * Test for getting asset id by store view of a product
     *
     * @magentoDataFixture Magento/MediaGallery/_files/media_asset.php
     * @magentoDataFixture Magento/MediaContentCatalog/_files/product_with_asset.php
     */
    public function testProductStoreView(): void
    {
        $this->assertEquals(
            [2020],
            $this->getAssetIdByContentField->execute(self::STORE_FIELD, (string)$this->storeId)
        );
    }

    /**
     * Test for getting asset id by enabled status of a product
     *
     * @magentoDataFixture Magento/MediaGallery/_files/media_asset.php
     * @magentoDataFixture Magento/MediaContentCatalog/_files/category_with_asset.php
     */
    public function testProductStatusEnabled(): void
    {
        $this->assertEquals(
            [2020],
            $this->getAssetIdByContentField->execute(self::STATUS_FIELD, self::STATUS_ENABLED)
        );
    }

    /**
     * Test for getting asset id by disabled status of a product
     *
     * @magentoDataFixture Magento/MediaGallery/_files/media_asset.php
     * @magentoDataFixture Magento/MediaContentCatalog/_files/product_with_asset.php
     */
    public function testProductStatusDisabled(): void
    {
        $this->assertEquals(
            [],
            $this->getAssetIdByContentField->execute(self::STATUS_FIELD, self::STATUS_DISABLED)
        );
    }
}
