<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Api;

use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Framework\Webapi\Rest\Request;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;

class GalleryManagementTest extends WebapiAbstract
{
    public const RESOURCE_PATH = '/V1/products/';

    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->fixtures = $this->objectManager->get(DataFixtureStorageManager::class)->getStorage();
    }

    /**
     * Check content attribute in getList method
     *
     * @return void
     */
    #[
        DataFixture(ProductFixture::class, ['media_gallery_entries' => [['label' => 'image1']]], as: 'product'),
    ]
    public function testContentAttributeInGetList(): void
    {
        $productSku = $this->fixtures->get('product')->getSku();
        $serviceInfo =  [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH.$productSku."/media",
                'httpMethod' => Request::HTTP_METHOD_GET,
            ],
        ];
        $response = $this->_webApiCall($serviceInfo, []);
        $this->assertArrayHasKey('content', $response[0]);
    }

    /**
     * Check content attribute in getList method
     *
     * @return void
     */
    #[
        DataFixture(ProductFixture::class, ['media_gallery_entries' => [['label' => 'image1']]], as: 'product'),
    ]
    public function testContentAttributeInGet(): void
    {
        $product = $this->fixtures->get('product');
        $productSku = $product->getSku();
        $entryId = $product-> getMediaGalleryEntries()[0]->getId();
        $serviceInfo =  [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH.$productSku."/media/".$entryId,
                'httpMethod' => Request::HTTP_METHOD_GET,
            ],
        ];
        $response = $this->_webApiCall($serviceInfo, []);
        $this->assertArrayHasKey('content', $response);
    }
}
