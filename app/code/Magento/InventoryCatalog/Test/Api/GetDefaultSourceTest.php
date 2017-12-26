<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Test\Api;

use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventoryCatalog\Api\DefaultSourceProviderInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\Framework\Webapi\Rest\Request;

class GetDefaultSourceTest extends WebapiAbstract
{
    /**
     * @var DefaultSourceProviderInterface
     */
    private $defaultSourceProvider;

    protected function setUp()
    {
        parent::setUp();
        $this->defaultSourceProvider = Bootstrap::getObjectManager()->get(DefaultSourceProviderInterface::class);
    }

    /**
     * Test that default Source is present after installation
     */
    public function testGetDefaultSource()
    {
        $defaultSourceCode = $this->defaultSourceProvider->getCode();
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/inventory/source/' . $defaultSourceCode,
                'httpMethod' => Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => 'inventoryApiSourceRepositoryV1',
                'operation' => 'inventoryApiSourceRepositoryV1Get',
            ],
        ];
        if (self::ADAPTER_REST == TESTS_WEB_API_ADAPTER) {
            $source = $this->_webApiCall($serviceInfo);
        } else {
            $source = $this->_webApiCall($serviceInfo, ['sourceCode' => $defaultSourceCode]);
        }
        $this->assertEquals($defaultSourceCode, $source[SourceInterface::SOURCE_CODE]);
    }
}
