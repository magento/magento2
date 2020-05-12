<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Backend\Test\Unit\Helper\Dashboard;

use Magento\Backend\Helper\Dashboard\Data as HelperData;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\ResourceModel\Store\Collection as StoreCollection;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DataTest extends TestCase
{
    /**
     * Stub path install
     */
    private const STUB_PATH_INSTALL = 'Sat, 6 Sep 2014 16:46:11 UTC';

    /**
     * Stub chart data hash
     */
    private const STUB_CHART_DATA_HASH = '52870842b23068a78220e01eb9d4404d';

    /**
     * @var HelperData
     */
    private $helper;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManagerMock;

    /**
     * @var DeploymentConfig|MockObject
     */
    private $deploymentConfigMock;

    /**
     * Prepare environment for test
     */
    protected function setUp(): void
    {
        $this->storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->deploymentConfigMock = $this->createMock(DeploymentConfig::class);
        $this->deploymentConfigMock->expects($this->once())->method('get')
            ->with(ConfigOptionsListConstants::CONFIG_PATH_INSTALL_DATE)
            ->willReturn(self::STUB_PATH_INSTALL);

        $objectManager = new ObjectManager($this);
        $this->helper = $objectManager->getObject(
            HelperData::class,
            [
                'storeManager' => $this->storeManagerMock,
                'deploymentConfig' => $this->deploymentConfigMock
            ]
        );
    }

    /**
     * Test getStores() when $_stores attribute is null
     */
    public function testGetStoresWhenStoreAttributeIsNull()
    {
        $storeMock = $this->createPartialMock(Store::class, ['getResourceCollection']);
        $storeCollectionMock = $this->createMock(StoreCollection::class);

        $storeCollectionMock->expects($this->once())->method('load')->willReturnSelf();
        $storeMock->expects($this->once())->method('getResourceCollection')->willReturn($storeCollectionMock);
        $this->storeManagerMock->expects($this->once())->method('getStore')->willReturn($storeMock);

        $this->assertEquals($storeCollectionMock, $this->helper->getStores());
    }

    /**
     * Test getChartDataHash() method
     */
    public function testGetChartDataHash()
    {
        $this->assertEquals(
            self::STUB_CHART_DATA_HASH,
            $this->helper->getChartDataHash(self::STUB_PATH_INSTALL)
        );
    }
}
