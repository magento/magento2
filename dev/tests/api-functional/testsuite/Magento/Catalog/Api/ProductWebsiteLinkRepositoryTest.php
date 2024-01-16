<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Api;

use Magento\Catalog\Model\ProductWebsiteLink;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * Tests to check products to websites assigning.
 *
 * @see \Magento\Catalog\Model\ProductWebsiteLinkRepository
 *
 * @magentoAppIsolation enabled
 */
class ProductWebsiteLinkRepositoryTest extends WebapiAbstract
{
    const SERVICE_NAME = 'catalogProductWebsiteLinkRepositoryV1';
    const SERVICE_VERSION = 'V1';

    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var WebsiteRepositoryInterface */
    private $websiteRepository;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $this->websiteRepository = $this->objectManager->get(WebsiteRepositoryInterface::class);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/second_product_simple.php
     *
     * @return void
     */
    public function testSaveWebsiteLinkWithUnexistingWebsiteId(): void
    {
        $pattern = '/(Could\\snot\\sassign\\sproduct)+([\\s\\S]*)(to\\swebsites)+([\\s\\S]*)/';
        $unexistingWebsiteId = 8932568989;
        $serviceInfo = $this->fillServiceInfo('/V1/products/:sku/websites', Request::HTTP_METHOD_POST, 'Save');
        $requestData = [
            'productWebsiteLink' => [
                ProductWebsiteLink::KEY_SKU => 'simple2',
                ProductWebsiteLink::WEBSITE_ID => $unexistingWebsiteId,
            ],
        ];
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches($pattern);
        $this->_webApiCall($serviceInfo, $requestData);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/product_with_two_websites.php
     *
     * @return void
     */
    public function testDeleteWebsiteLink(): void
    {
        $productSku = 'unique-simple-azaza';
        $websiteId = (int)$this->websiteRepository->get('second_website')->getId();
        $resourcePath = sprintf('/V1/products/%s/websites/%u', $productSku, $websiteId);
        $serviceInfo = $this->fillServiceInfo($resourcePath, Request::HTTP_METHOD_DELETE, 'DeleteById');
        $this->_webApiCall(
            $serviceInfo,
            [ProductWebsiteLink::KEY_SKU => $productSku, ProductWebsiteLink::WEBSITE_ID => $websiteId]
        );
        $product = $this->productRepository->get($productSku, false, null, true);
        $this->assertNotContains($websiteId, $product->getWebsiteIds());
    }

    /**
     * Fill service information
     *
     * @param string $resourcePath
     * @param string $httpMethod
     * @param string $operation
     * @return array
     */
    private function fillServiceInfo(string $resourcePath, string $httpMethod, string $operation): array
    {
        return [
            'rest' => ['resourcePath' => $resourcePath, 'httpMethod' => $httpMethod],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . $operation,
            ],
        ];
    }
}
