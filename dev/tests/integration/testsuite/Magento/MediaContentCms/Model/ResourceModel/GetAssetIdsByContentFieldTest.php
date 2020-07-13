<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 *
 */
declare(strict_types=1);

namespace Magento\MediaContentCms\Model\ResourceModel;

use Magento\MediaContentApi\Api\GetAssetIdsByContentFieldInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test for GetAssetIdByContentFieldTest
 */
class GetAssetIdsByContentFieldTest extends TestCase
{
    private const STORE_FIELD = 'store_id';
    private const STATUS_FIELD = 'content_status';
    private const STATUS_ENABLED = '1';
    private const STATUS_DISABLED = '0';

    /**
     * @var GetAssetIdsByContentFieldInterface
     */
    private $getAssetIdsByContentField;

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
        $this->getAssetIdsByContentField = $objectManager->get(GetAssetIdsByContentFieldInterface::class);
    }

    /**
     * Test for getting asset id by store view of a block
     *
     * @magentoDataFixture Magento/MediaGallery/_files/media_asset.php
     * @magentoDataFixture Magento/MediaContentCms/_files/block_with_asset.php
     */
    public function testBlockStoreView(): void
    {
        $this->assertEquals(
            [2020],
            $this->getAssetIdsByContentField->execute(self::STORE_FIELD, (string)$this->storeId)
        );
    }

    /**
     * Test for getting asset id by enabled status of a page
     *
     * @magentoDataFixture Magento/MediaGallery/_files/media_asset.php
     * @magentoDataFixture Magento/MediaContentCms/_files/page_with_asset.php
     */
    public function testPageStatusEnabled(): void
    {
        $this->assertEquals(
            [2020],
            $this->getAssetIdsByContentField->execute(self::STATUS_FIELD, self::STATUS_ENABLED)
        );
    }

    /**
     * Test for getting asset id by disabled status of a page
     *
     * @magentoDataFixture Magento/MediaGallery/_files/media_asset.php
     * @magentoDataFixture Magento/MediaContentCms/_files/page_with_asset.php
     */
    public function testPageStatusDisabled(): void
    {
        $this->assertEquals(
            [],
            $this->getAssetIdsByContentField->execute(self::STATUS_FIELD, self::STATUS_DISABLED)
        );
    }
}
