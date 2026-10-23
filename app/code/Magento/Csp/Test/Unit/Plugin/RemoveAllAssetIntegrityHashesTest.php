<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Csp\Test\Unit\Plugin;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Csp\Plugin\RemoveAllAssetIntegrityHashes;
use Magento\Csp\Model\SubresourceIntegrityCollector;
use Magento\Csp\Model\SubresourceIntegrityRepositoryPool;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Exception\FileSystemException;
use Magento\Deploy\Service\DeployStaticContent;
use Magento\Deploy\Console\DeployStaticOptions;
use Psr\Log\LoggerInterface;

/**
 * Unit tests for RemoveAllAssetIntegrityHashes plugin
 */
class RemoveAllAssetIntegrityHashesTest extends TestCase
{
    /**
     * @var RemoveAllAssetIntegrityHashes
     */
    private RemoveAllAssetIntegrityHashes $plugin;

    /**
     * @var MockObject|SubresourceIntegrityRepositoryPool
     */
    private MockObject $repositoryPoolMock;

    /**
     * @var MockObject|SubresourceIntegrityCollector
     */
    private MockObject $integrityCollectorMock;

    /**
     * @var MockObject|Filesystem
     */
    private MockObject $filesystemMock;

    /**
     * @var MockObject|LoggerInterface
     */
    private MockObject $loggerMock;

    /**
     * @var MockObject|WriteInterface
     */
    private MockObject $directoryMock;

    protected function setUp(): void
    {
        $this->repositoryPoolMock = $this->createMock(SubresourceIntegrityRepositoryPool::class);
        $this->integrityCollectorMock = $this->createMock(SubresourceIntegrityCollector::class);
        $this->filesystemMock = $this->createMock(Filesystem::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->directoryMock = $this->createMock(WriteInterface::class);

        $this->filesystemMock->method('getDirectoryWrite')->willReturn($this->directoryMock);

        $this->plugin = new RemoveAllAssetIntegrityHashes(
            $this->repositoryPoolMock,
            $this->integrityCollectorMock,
            $this->filesystemMock,
            $this->loggerMock
        );
    }

    /**
     * Test collector is cleared during deploy
     */
    public function testBeforeDeployClearsCollector(): void
    {
        $subjectMock = $this->createMock(DeployStaticContent::class);

        $this->directoryMock->method('search')->willReturn([]);

        $this->integrityCollectorMock->expects($this->once())->method('clear');

        $this->plugin->beforeDeploy($subjectMock, []);
    }

    /**
     * Test refresh version only option skips deletion
     */
    public function testBeforeDeploySkipsOnRefreshVersionOnly(): void
    {
        $subjectMock = $this->createMock(DeployStaticContent::class);

        $options = [DeployStaticOptions::REFRESH_CONTENT_VERSION_ONLY => true];

        $this->directoryMock->expects($this->never())->method('search');
        $this->integrityCollectorMock->expects($this->never())->method('clear');

        $this->plugin->beforeDeploy($subjectMock, $options);
    }

    /**
     * Test filesystem exception is logged but doesn't fail deployment
     */
    public function testBeforeDeployLogsFilesystemError(): void
    {
        $subjectMock = $this->createMock(DeployStaticContent::class);

        $filesystemMock = $this->createMock(Filesystem::class);
        $filesystemMock->method('getDirectoryWrite')
            ->willThrowException(new FileSystemException(__('Filesystem error')));

        $plugin = new RemoveAllAssetIntegrityHashes(
            $this->repositoryPoolMock,
            $this->integrityCollectorMock,
            $filesystemMock,
            $this->loggerMock
        );

        $this->loggerMock->expects($this->once())
            ->method('warning')
            ->with($this->stringContains('Failed to delete SRI files'));

        $this->integrityCollectorMock->expects($this->once())->method('clear');

        $plugin->beforeDeploy($subjectMock, []);
    }

    /**
     * Test no files to delete doesn't cause errors
     */
    public function testBeforeDeployHandlesNoFiles(): void
    {
        $subjectMock = $this->createMock(DeployStaticContent::class);

        $this->directoryMock->method('search')->willReturn([]);
        $this->directoryMock->expects($this->never())->method('delete');

        $this->plugin->beforeDeploy($subjectMock, []);
    }

    /**
     * Test search exception is handled gracefully
     */
    public function testBeforeDeployHandlesSearchException(): void
    {
        $subjectMock = $this->createMock(DeployStaticContent::class);

        $this->directoryMock->method('search')
            ->willThrowException(new FileSystemException(__('Search failed')));

        $this->loggerMock->expects($this->once())->method('warning');
        $this->integrityCollectorMock->expects($this->once())->method('clear');

        $this->plugin->beforeDeploy($subjectMock, []);
    }

    /**
     * Test delete exception is handled gracefully
     */
    public function testBeforeDeployHandlesDeleteException(): void
    {
        $subjectMock = $this->createMock(DeployStaticContent::class);

        $this->directoryMock->method('search')
            ->willReturn(['frontend/Magento/luma/en_US/sri-hashes.json']);

        $this->directoryMock->method('delete')
            ->willThrowException(new FileSystemException(__('Delete failed')));

        $this->loggerMock->expects($this->once())->method('warning');

        $this->plugin->beforeDeploy($subjectMock, []);
    }

    /**
     * Test scoped deploy with area only uses wildcards for theme and locale
     */
    public function testBeforeDeployWithAreaOnlySearchesWildcardThemeAndLocale(): void
    {
        $subjectMock = $this->createMock(DeployStaticContent::class);

        $this->directoryMock->expects($this->once())
            ->method('search')
            ->with('frontend/*/*/*/sri-hashes.json')
            ->willReturn([]);

        $this->directoryMock->method('isFile')->willReturn(false);

        $options = [DeployStaticOptions::AREA => ['frontend']];
        $this->plugin->beforeDeploy($subjectMock, $options);
    }

    /**
     * Test scoped deploy with theme only uses wildcards for area and locale
     */
    public function testBeforeDeployWithThemeOnlySearchesWildcardAreaAndLocale(): void
    {
        $subjectMock = $this->createMock(DeployStaticContent::class);

        $this->directoryMock->expects($this->once())
            ->method('search')
            ->with('*/Magento/luma/*/sri-hashes.json')
            ->willReturn([]);

        $this->directoryMock->method('isFile')->willReturn(false);

        $options = [DeployStaticOptions::THEME => ['Magento/luma']];
        $this->plugin->beforeDeploy($subjectMock, $options);
    }

    /**
     * Test scoped deploy with locale only uses wildcards for area and theme
     */
    public function testBeforeDeployWithLocaleOnlySearchesWildcardAreaAndTheme(): void
    {
        $subjectMock = $this->createMock(DeployStaticContent::class);

        $this->directoryMock->expects($this->once())
            ->method('search')
            ->with('*/*/*/en_US/sri-hashes.json')
            ->willReturn([]);

        $this->directoryMock->method('isFile')->willReturn(false);

        $options = [DeployStaticOptions::LANGUAGE => ['en_US']];
        $this->plugin->beforeDeploy($subjectMock, $options);
    }

    /**
     * Test scoped deploy with all three constraints builds exact pattern
     */
    public function testBeforeDeployWithFullScopeSearchesExactPattern(): void
    {
        $subjectMock = $this->createMock(DeployStaticContent::class);

        $matchedFile = 'frontend/Magento/luma/en_US/sri-hashes.json';

        $this->directoryMock->expects($this->once())
            ->method('search')
            ->with('frontend/Magento/luma/en_US/sri-hashes.json')
            ->willReturn([$matchedFile]);

        $this->directoryMock->expects($this->once())
            ->method('delete')
            ->with($matchedFile);

        $this->directoryMock->method('isFile')->willReturn(false);

        $options = [
            DeployStaticOptions::AREA => ['frontend'],
            DeployStaticOptions::THEME => ['Magento/luma'],
            DeployStaticOptions::LANGUAGE => ['en_US'],
        ];
        $this->plugin->beforeDeploy($subjectMock, $options);
    }

    /**
     * Test scoped deploy deletes the merged cache file when present
     */
    public function testBeforeDeployWithScopeDeletesMergedCacheFile(): void
    {
        $subjectMock = $this->createMock(DeployStaticContent::class);

        $this->directoryMock->method('search')->willReturn([]);
        $this->directoryMock->method('isFile')->willReturn(true);

        $this->directoryMock->expects($this->once())
            ->method('delete')
            ->with('_cache/merged/sri-hashes.json');

        $options = [DeployStaticOptions::AREA => ['frontend']];
        $this->plugin->beforeDeploy($subjectMock, $options);
    }
}
