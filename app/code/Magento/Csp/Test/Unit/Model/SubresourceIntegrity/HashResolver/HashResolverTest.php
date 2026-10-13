<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Csp\Test\Unit\Model\SubresourceIntegrity\HashResolver;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Csp\Model\SubresourceIntegrity\HashResolver\HashResolver;
use Magento\Csp\Model\SubresourceIntegrityRepositoryPool;
use Magento\Csp\Model\SubresourceIntegrityRepository;
use Magento\Csp\Model\SubresourceIntegrity;
use Magento\Framework\App\State;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\DesignInterface;
use Magento\Framework\View\Design\ThemeInterface;
use Psr\Log\LoggerInterface;
use Exception;

/**
 * Unit tests for HashResolver
 *
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class HashResolverTest extends TestCase
{
    /**
     * @var HashResolver
     */
    private HashResolver $resolver;

    /**
     * @var MockObject|SubresourceIntegrityRepositoryPool
     */
    private MockObject $repositoryPoolMock;

    /**
     * @var MockObject|State
     */
    private MockObject $appStateMock;

    /**
     * @var MockObject|DesignInterface
     */
    private MockObject $designMock;

    /**
     * @var MockObject|UrlInterface
     */
    private MockObject $urlBuilderMock;

    /**
     * @var MockObject|LoggerInterface
     */
    private MockObject $loggerMock;

    /**
     * @var MockObject|Filesystem
     */
    private MockObject $filesystemMock;

    /**
     * @var MockObject|SerializerInterface
     */
    private MockObject $serializerMock;

    protected function setUp(): void
    {
        $this->repositoryPoolMock = $this->createMock(SubresourceIntegrityRepositoryPool::class);
        $this->appStateMock = $this->createMock(State::class);
        $this->designMock = $this->createMock(DesignInterface::class);
        $this->urlBuilderMock = $this->createMock(UrlInterface::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->filesystemMock = $this->createMock(Filesystem::class);
        $this->serializerMock = $this->createMock(SerializerInterface::class);

        $staticDirMock = $this->createMock(ReadInterface::class);
        $staticDirMock->method('search')->willReturn([]);
        $this->filesystemMock->method('getDirectoryRead')->willReturn($staticDirMock);

        $this->resolver = new HashResolver(
            $this->repositoryPoolMock,
            $this->appStateMock,
            $this->designMock,
            $this->urlBuilderMock,
            $this->loggerMock,
            $this->filesystemMock,
            $this->serializerMock
        );
    }

    /**
     * Creates a HashResolver configured for compact deploy.
     *
     * Mocks search() to return a non-empty map.json result so resolveCompactDeploy() returns true.
     *
     * @return HashResolver
     */
    private function createCompactResolver(): HashResolver
    {
        $staticDirMock = $this->createMock(ReadInterface::class);
        $staticDirMock->method('search')->willReturn(['frontend/Magento/luma/en_US/result_map.json']);

        $filesystemMock = $this->createMock(Filesystem::class);
        $filesystemMock->method('getDirectoryRead')->willReturn($staticDirMock);

        return new HashResolver(
            $this->repositoryPoolMock,
            $this->appStateMock,
            $this->designMock,
            $this->urlBuilderMock,
            $this->loggerMock,
            $filesystemMock,
            $this->serializerMock
        );
    }

    /**
     * Test getAllHashes loads from all contexts
     */
    public function testGetAllHashesLoadsFromAllContexts(): void
    {
        $this->appStateMock->method('getAreaCode')->willReturn('frontend');
        $this->designMock->method('getLocale')->willReturn('en_US');
        $this->urlBuilderMock->method('getBaseUrl')->willReturn('https://example.com/static/');

        // Setup theme with parent
        $parentThemeMock = $this->createMock(ThemeInterface::class);
        $parentThemeMock->method('getParentTheme')->willReturn(null);

        $themeMock = $this->createMock(ThemeInterface::class);
        $themeMock->method('getParentTheme')->willReturn($parentThemeMock);

        $this->designMock->method('getDesignTheme')->willReturn($themeMock);
        $this->designMock->method('getThemePath')
            ->willReturnCallback(function ($theme) use ($themeMock) {
                if ($theme === $themeMock) {
                    return 'Magento/luma';
                }
                return 'Magento/blank';
            });

        // Create integrity mocks
        $baseIntegrity = $this->createMock(SubresourceIntegrity::class);
        $baseIntegrity->method('getPath')->willReturn('base/Magento/base/default/jquery.js');
        $baseIntegrity->method('getHash')->willReturn('sha256-base');

        $themeIntegrity = $this->createMock(SubresourceIntegrity::class);
        $themeIntegrity->method('getPath')->willReturn('frontend/Magento/luma/en_US/theme.js');
        $themeIntegrity->method('getHash')->willReturn('sha256-theme');

        // Setup repositories
        $baseRepo = $this->createMock(SubresourceIntegrityRepository::class);
        $baseRepo->method('getAll')->willReturn([$baseIntegrity]);

        $emptyRepo = $this->createMock(SubresourceIntegrityRepository::class);
        $emptyRepo->method('getAll')->willReturn([]);

        $themeRepo = $this->createMock(SubresourceIntegrityRepository::class);
        $themeRepo->method('getAll')->willReturn([$themeIntegrity]);

        $this->repositoryPoolMock->method('get')
            ->willReturnCallback(function ($context) use ($baseRepo, $emptyRepo, $themeRepo) {
                if ($context === 'base/Magento/base/default') {
                    return $baseRepo;
                }
                if ($context === 'frontend/Magento/luma/en_US') {
                    return $themeRepo;
                }
                return $emptyRepo;
            });

        $result = $this->resolver->getAllHashes();

        $this->assertArrayHasKey('https://example.com/static/base/Magento/base/default/jquery.js', $result);
        $this->assertArrayHasKey('https://example.com/static/frontend/Magento/luma/en_US/theme.js', $result);
        $this->assertEquals('sha256-base', $result['https://example.com/static/base/Magento/base/default/jquery.js']);
        $this->assertEquals('sha256-theme', $result['https://example.com/static/frontend/Magento/luma/en_US/theme.js']);
    }

    /**
     * Test getHashByPath finds hash in base context
     */
    public function testGetHashByPathFindsInBase(): void
    {
        $this->appStateMock->method('getAreaCode')->willReturn('frontend');
        $this->designMock->method('getLocale')->willReturn('en_US');

        $themeMock = $this->createMock(ThemeInterface::class);
        $themeMock->method('getParentTheme')->willReturn(null);
        $this->designMock->method('getDesignTheme')->willReturn($themeMock);
        $this->designMock->method('getThemePath')->willReturn('Magento/luma');

        $integrity = $this->createMock(SubresourceIntegrity::class);
        $integrity->method('getHash')->willReturn('sha256-found');

        $baseRepo = $this->createMock(SubresourceIntegrityRepository::class);
        $baseRepo->method('getByPath')->willReturn($integrity);

        $this->repositoryPoolMock->method('get')
            ->with('base/Magento/base/default')
            ->willReturn($baseRepo);

        $result = $this->resolver->getHashByPath('jquery.js');

        $this->assertEquals('sha256-found', $result);
    }

    /**
     * Test getHashByPath returns null when not found
     */
    public function testGetHashByPathReturnsNullWhenNotFound(): void
    {
        $this->appStateMock->method('getAreaCode')->willReturn('frontend');
        $this->designMock->method('getLocale')->willReturn('en_US');

        $themeMock = $this->createMock(ThemeInterface::class);
        $themeMock->method('getParentTheme')->willReturn(null);
        $this->designMock->method('getDesignTheme')->willReturn($themeMock);
        $this->designMock->method('getThemePath')->willReturn('Magento/luma');

        $emptyRepo = $this->createMock(SubresourceIntegrityRepository::class);
        $emptyRepo->method('getByPath')->willReturn(null);

        $this->repositoryPoolMock->method('get')->willReturn($emptyRepo);

        $result = $this->resolver->getHashByPath('nonexistent.js');

        $this->assertNull($result);
    }

    /**
     * Test getHashByPath checks full theme hierarchy on compact deploy
     */
    public function testGetHashByPathChecksThemeHierarchyOnCompactDeploy(): void
    {
        $resolver = $this->createCompactResolver();

        $this->appStateMock->method('getAreaCode')->willReturn('frontend');
        $this->designMock->method('getLocale')->willReturn('en_US');

        // Setup Luma -> Blank hierarchy
        $blankThemeMock = $this->createMock(ThemeInterface::class);
        $blankThemeMock->method('getParentTheme')->willReturn(null);

        $lumaThemeMock = $this->createMock(ThemeInterface::class);
        $lumaThemeMock->method('getParentTheme')->willReturn($blankThemeMock);

        $this->designMock->method('getDesignTheme')->willReturn($lumaThemeMock);
        $this->designMock->method('getThemePath')
            ->willReturnCallback(function ($theme) use ($lumaThemeMock) {
                if ($theme === $lumaThemeMock) {
                    return 'Magento/luma';
                }
                return 'Magento/blank';
            });

        $integrity = $this->createMock(SubresourceIntegrity::class);
        $integrity->method('getHash')->willReturn('sha256-from-blank');

        $emptyRepo = $this->createMock(SubresourceIntegrityRepository::class);
        $emptyRepo->method('getByPath')->willReturn(null);

        $blankRepo = $this->createMock(SubresourceIntegrityRepository::class);
        $blankRepo->method('getByPath')->willReturn($integrity);

        $this->repositoryPoolMock->method('get')
            ->willReturnCallback(function ($context) use ($emptyRepo, $blankRepo) {
                if ($context === 'frontend/Magento/blank/en_US') {
                    return $blankRepo;
                }
                return $emptyRepo;
            });

        $result = $resolver->getHashByPath('theme.js');

        $this->assertEquals('sha256-from-blank', $result);
    }

    /**
     * Test getAllHashes handles repository exception gracefully
     */
    public function testGetAllHashesHandlesRepositoryException(): void
    {
        $this->appStateMock->method('getAreaCode')->willReturn('frontend');
        $this->designMock->method('getLocale')->willReturn('en_US');
        $this->urlBuilderMock->method('getBaseUrl')->willReturn('https://example.com/static/');

        $themeMock = $this->createMock(ThemeInterface::class);
        $themeMock->method('getParentTheme')->willReturn(null);
        $this->designMock->method('getDesignTheme')->willReturn($themeMock);
        $this->designMock->method('getThemePath')->willReturn('Magento/luma');

        // First context throws, others work
        $goodIntegrity = $this->createMock(SubresourceIntegrity::class);
        $goodIntegrity->method('getPath')->willReturn('frontend/Magento/luma/en_US/file.js');
        $goodIntegrity->method('getHash')->willReturn('sha256-good');

        $goodRepo = $this->createMock(SubresourceIntegrityRepository::class);
        $goodRepo->method('getAll')->willReturn([$goodIntegrity]);

        $callCount = 0;
        $this->repositoryPoolMock->method('get')
            ->willReturnCallback(function () use ($goodRepo, &$callCount) {
                $callCount++;
                if ($callCount === 1) {
                    throw new Exception('Repository error');
                }
                return $goodRepo;
            });

        $this->loggerMock->expects($this->atLeastOnce())->method('debug');

        $result = $this->resolver->getAllHashes();

        // Should still return results from working contexts
        $this->assertNotEmpty($result);
    }

    /**
     * Test getHashByPath handles exception gracefully
     */
    public function testGetHashByPathHandlesExceptionGracefully(): void
    {
        $this->appStateMock->method('getAreaCode')
            ->willThrowException(new Exception('Area not set'));

        $this->loggerMock->expects($this->atLeastOnce())->method('debug');

        // Base context should still be checked
        $emptyRepo = $this->createMock(SubresourceIntegrityRepository::class);
        $emptyRepo->method('getByPath')->willReturn(null);

        $this->repositoryPoolMock->method('get')->willReturn($emptyRepo);

        $result = $this->resolver->getHashByPath('file.js');

        // Should return null gracefully, not throw
        $this->assertNull($result);
    }

    /**
     * Test getAllHashes returns empty array on complete failure
     */
    public function testGetAllHashesReturnsEmptyOnCompleteFailure(): void
    {
        $this->urlBuilderMock->method('getBaseUrl')
            ->willThrowException(new Exception('URL builder error'));

        $this->loggerMock->expects($this->once())
            ->method('warning')
            ->with(
                'SRI: Failed to load all hashes',
                $this->callback(function ($context) {
                    return isset($context['exception']);
                })
            );

        $result = $this->resolver->getAllHashes();

        $this->assertEmpty($result);
    }

    /**
     * Test getHashByPath logs and returns null on context load failure
     */
    public function testGetHashByPathLogsOnContextFailure(): void
    {
        $this->appStateMock->method('getAreaCode')->willReturn('frontend');
        $this->designMock->method('getLocale')->willReturn('en_US');

        $themeMock = $this->createMock(ThemeInterface::class);
        $themeMock->method('getParentTheme')->willReturn(null);
        $this->designMock->method('getDesignTheme')->willReturn($themeMock);
        $this->designMock->method('getThemePath')->willReturn('Magento/luma');

        $this->repositoryPoolMock->method('get')
            ->willThrowException(new Exception('Repository pool error'));

        $this->loggerMock->expects($this->atLeastOnce())->method('debug');

        $result = $this->resolver->getHashByPath('file.js');

        $this->assertNull($result);
    }

    /**
     * Test getAllHashes does not walk parent themes on standard/quick deploy
     */
    public function testGetAllHashesDoesNotWalkParentThemesOnStandardDeploy(): void
    {
        // Default setUp uses non-compact deploy (search returns empty array — no map.json files)
        $this->appStateMock->method('getAreaCode')->willReturn('frontend');
        $this->designMock->method('getLocale')->willReturn('en_US');
        $this->urlBuilderMock->method('getBaseUrl')->willReturn('https://example.com/static/');

        $parentThemeMock = $this->createMock(ThemeInterface::class);
        $parentThemeMock->method('getParentTheme')->willReturn(null);

        $themeMock = $this->createMock(ThemeInterface::class);
        $themeMock->method('getParentTheme')->willReturn($parentThemeMock);

        $this->designMock->method('getDesignTheme')->willReturn($themeMock);
        $this->designMock->method('getThemePath')
            ->willReturnCallback(function ($theme) use ($themeMock) {
                return $theme === $themeMock ? 'Magento/luma' : 'Magento/blank';
            });

        $parentIntegrity = $this->createMock(SubresourceIntegrity::class);
        $parentIntegrity->method('getPath')->willReturn('frontend/Magento/blank/default/parent.js');
        $parentIntegrity->method('getHash')->willReturn('sha256-parent');

        $parentRepo = $this->createMock(SubresourceIntegrityRepository::class);
        $parentRepo->method('getAll')->willReturn([$parentIntegrity]);

        $emptyRepo = $this->createMock(SubresourceIntegrityRepository::class);
        $emptyRepo->method('getAll')->willReturn([]);

        $this->repositoryPoolMock->method('get')
            ->willReturnCallback(function ($context) use ($parentRepo, $emptyRepo) {
                return str_contains($context, 'Magento/blank') ? $parentRepo : $emptyRepo;
            });

        $result = $this->resolver->getAllHashes();

        // Parent theme (blank) context must NOT be loaded on non-compact deploy
        $this->assertArrayNotHasKey(
            'https://example.com/static/frontend/Magento/blank/default/parent.js',
            $result
        );
    }

    /**
     * Test getAllHashes walks full parent theme chain on compact deploy
     */
    public function testGetAllHashesWalksParentThemesOnCompactDeploy(): void
    {
        $resolver = $this->createCompactResolver();

        $this->appStateMock->method('getAreaCode')->willReturn('frontend');
        $this->designMock->method('getLocale')->willReturn('en_US');
        $this->urlBuilderMock->method('getBaseUrl')->willReturn('https://example.com/static/');

        $parentThemeMock = $this->createMock(ThemeInterface::class);
        $parentThemeMock->method('getParentTheme')->willReturn(null);

        $themeMock = $this->createMock(ThemeInterface::class);
        $themeMock->method('getParentTheme')->willReturn($parentThemeMock);

        $this->designMock->method('getDesignTheme')->willReturn($themeMock);
        $this->designMock->method('getThemePath')
            ->willReturnCallback(function ($theme) use ($themeMock) {
                return $theme === $themeMock ? 'Magento/luma' : 'Magento/blank';
            });

        $parentIntegrity = $this->createMock(SubresourceIntegrity::class);
        $parentIntegrity->method('getPath')->willReturn('frontend/Magento/blank/default/parent.js');
        $parentIntegrity->method('getHash')->willReturn('sha256-parent');

        $parentRepo = $this->createMock(SubresourceIntegrityRepository::class);
        $parentRepo->method('getAll')->willReturn([$parentIntegrity]);

        $emptyRepo = $this->createMock(SubresourceIntegrityRepository::class);
        $emptyRepo->method('getAll')->willReturn([]);

        $this->repositoryPoolMock->method('get')
            ->willReturnCallback(function ($context) use ($parentRepo, $emptyRepo) {
                return str_contains($context, 'Magento/blank') ? $parentRepo : $emptyRepo;
            });

        $result = $resolver->getAllHashes();

        // Parent theme (blank) context MUST be loaded on compact deploy
        $this->assertArrayHasKey(
            'https://example.com/static/frontend/Magento/blank/default/parent.js',
            $result
        );
        $this->assertEquals(
            'sha256-parent',
            $result['https://example.com/static/frontend/Magento/blank/default/parent.js']
        );
    }
}
