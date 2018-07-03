<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Test\Api;

use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventoryCatalogApi\Api\DefaultSourceProviderInterface;
use Magento\InventoryCatalogApi\Api\DefaultStockProviderInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\Framework\Webapi\Rest\Request;

class GetDefaultStockToSourceLinkTest extends WebapiAbstract
{
    /**
     * @var DefaultSourceProviderInterface
     */
    private $defaultSourceProvider;

    /**
     * @var DefaultStockProviderInterface
     */
    private $defaultStockProvider;

    protected function setUp()
    {
        parent::setUp();
        $this->defaultSourceProvider = Bootstrap::getObjectManager()->get(DefaultSourceProviderInterface::class);
        $this->defaultStockProvider = Bootstrap::getObjectManager()->get(DefaultStockProviderInterface::class);
    }

    /**
     * Test that default Stock is present after installation
     */
    public function testGetDefaultStockToSourceLink()
    {
        $defaultStockId = $this->defaultStockProvider->getId();
        $defaultSourceCode = $this->defaultSourceProvider->getCode();
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/inventory/get-sources-assigned-to-stock-ordered-by-priority/' . $defaultStockId,
                'httpMethod' => Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => 'inventoryApiGetSourcesAssignedToStockOrderedByPriorityV1',
                'operation' => 'inventoryApiGetSourcesAssignedToStockOrderedByPriorityV1Execute',
            ],
        ];
        if (self::ADAPTER_REST === TESTS_WEB_API_ADAPTER) {
            $source = $this->_webApiCall($serviceInfo);
        } else {
            $source = $this->_webApiCall($serviceInfo, ['stockId' => $defaultStockId]);
        }
        $this->assertEquals([$defaultSourceCode], array_column($source, SourceInterface::SOURCE_CODE));
    }
}
