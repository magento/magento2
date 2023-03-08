<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\Test\Unit\Model\Config\Processor;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\ResourceConnection;
use Magento\Store\App\Config\Type\Scopes;
use Magento\Store\Model\Config\Processor\Fallback;
use Magento\Store\Model\ResourceModel\Store;
use Magento\Store\Model\ResourceModel\Website;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FallbackTest extends TestCase
{
    /**
     * @var Scopes|Scopes&MockObject|MockObject
     */
    private Scopes $scopes;
    /**
     * @var ResourceConnection|ResourceConnection&MockObject|MockObject
     */
    private ResourceConnection $resourceConnection;
    /**
     * @var Store|Store&MockObject|MockObject
     */
    private Store $storeResource;
    /**
     * @var Website|Website&MockObject|MockObject
     */
    private Website $websiteResource;
    /**
     * @var DeploymentConfig|DeploymentConfig&MockObject|MockObject
     */
    private DeploymentConfig $deploymentConfig;
    /**
     * @var Fallback
     */
    private Fallback $fallback;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->scopes = $this->createMock(Scopes::class);
        $this->resourceConnection = $this->createMock(ResourceConnection::class);
        $this->storeResource = $this->createMock(Store::class);
        $this->websiteResource = $this->createMock(Website::class);
        $this->deploymentConfig = $this->createMock(DeploymentConfig::class);
        $this->fallback = new Fallback(
            $this->scopes,
            $this->resourceConnection,
            $this->storeResource,
            $this->websiteResource,
            $this->deploymentConfig
        );
    }

    /**
     * @return void
     */
    public function testProcessWithStoreCodeCapitalLetters()
    {
        $storesData = $this->getStoresData();
        $websiteData = $this->getWebsitesData();
        $this->deploymentConfig->expects($this->once())->method('isDbAvailable')->willReturn(true);
        $this->storeResource->expects($this->once())->method('readAllStores')->willReturn($storesData);
        $this->websiteResource->expects($this->once())->method('readAllWebsites')->willReturn($websiteData);

        $result = $this->fallback->process(
            [
                'stores' => [
                    'TWO' => [
                        'checkout' => [
                            'options' => ['guest_checkout' => 0]
                        ]
                    ]
                ],
                'websites' => [
                    ['admin' => ['web' => ['routers' => ['frontend' => ['disabled' => true]]]]]
                ]
            ]
        );
        $this->assertTrue(in_array('two', array_keys($result['stores'])));
    }

    /**
     * Sample stores data
     *
     * @return array[]
     */
    private function getStoresData(): array
    {
        return [
            [
                'store_id' => 0,
                'code' => 'admin',
                'website_id' => 0,
                'group_id' => 0,
                'name' => 'Admin',
                'sort_order' => 0,
                'is_active' => 1
            ],
            [
                'store_id' => 1,
                'code' => 'default',
                'website_id' => 1,
                'group_id' => 1,
                'name' => 'Default Store View',
                'sort_order' => 0,
                'is_active' => 1
            ],
            [
                'store_id' => 2,
                'code' => 'TWO',
                'website_id' => 1,
                'group_id' => 1,
                'name' => 'TWO',
                'sort_order' => 0,
                'is_active' => 1
            ]
        ];
    }

    private function getWebsitesData(): array
    {
        return [
            [
                'website_id' => 0,
                'code' => 'admin',
                'name' => 'Admin',
                'sort_order' => 0,
                'default_group_id' => 0,
                'is_default' => 0
            ],
            [
                'website_id' => 1,
                'code' => 'base',
                'name' => 'Main Website',
                'sort_order' => 0,
                'default_group_id' => 1,
                'is_default' => 1
            ]
        ];
    }
}
