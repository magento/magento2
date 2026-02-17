<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Csp\Test\Unit\Plugin;

use Magento\Csp\Model\SubresourceIntegrity;
use Magento\Csp\Model\SubresourceIntegrityRepository;
use Magento\Csp\Model\SubresourceIntegrityRepositoryPool;
use Magento\Deploy\Service\DeployStaticContent;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Csp\Plugin\StoreAssetIntegrityHashes;
use Magento\Csp\Model\SubresourceIntegrityCollector;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Plugin that removes existing integrity hashes for all assets.
 */
class StoreAssetIntegrityHashesTest extends TestCase
{
    /**
     * @var MockObject
     */
    private MockObject $integrityRepositoryPoolMock;

    /**
     * @var MockObject
     */
    private MockObject $integrityCollectorMock;

    /**
     * @var MockObject
     */
    private MockObject $loggerMock;

    /**
     * @var StoreAssetIntegrityHashes
     */
    private StoreAssetIntegrityHashes $plugin;

    /**
     * Initialize Dependencies
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->integrityRepositoryPoolMock = $this->createPartialMock(
            SubresourceIntegrityRepositoryPool::class,
            ['get']
        );
        $this->integrityCollectorMock = $this->createPartialMock(
            SubresourceIntegrityCollector::class,
            ['release']
        );
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->plugin = new StoreAssetIntegrityHashes(
            $this->integrityCollectorMock,
            $this->integrityRepositoryPoolMock,
            $this->loggerMock
        );
    }

    /**
     * Test After Deploy method of plugin
     *
     * @return void
     */
    public function testAfterDeploy(): void
    {
        $bunch1 = new SubresourceIntegrity(
            [
                'hash' => 'testhash',
                'path' => 'adminhtml/js/jquery.js'
            ]
        );

        $bunch2 = new SubresourceIntegrity(
            [
                'hash' => 'testhash2',
                'path' => 'frontend/js/test.js'
            ]
        );

        $bunches = [$bunch1, $bunch2];
        $deployStaticContentMock = $this->createMock(DeployStaticContent::class);
        $subResourceIntegrityMock = $this->createPartialMock(
            SubresourceIntegrityRepository::class,
            ['saveBunch']
        );
        $this->integrityCollectorMock->expects($this->once())->method('release')->willReturn($bunches);
        $this->integrityRepositoryPoolMock->expects($this->any())->method('get')->willReturn($subResourceIntegrityMock);
        $subResourceIntegrityMock->expects($this->any())->method('saveBunch')->willReturn(true);
        $this->plugin->afterDeploy($deployStaticContentMock, null, []);
    }
}
