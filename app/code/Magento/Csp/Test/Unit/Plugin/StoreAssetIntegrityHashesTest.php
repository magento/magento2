<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Csp\Test\Unit\Plugin;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\Attributes\DataProvider;
use Magento\Csp\Plugin\StoreAssetIntegrityHashes;
use Magento\Csp\Model\SubresourceIntegrityCollector;
use Magento\Csp\Model\SubresourceIntegrityRepositoryPool;
use Magento\Csp\Model\SubresourceIntegrityRepository;
use Magento\Csp\Model\SubresourceIntegrity;
use Magento\Deploy\Console\DeployStaticOptions;
use Magento\Deploy\Service\DeployStaticContent;
use Psr\Log\LoggerInterface;
use Exception;

/**
 * Unit tests for StoreAssetIntegrityHashes plugin
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class StoreAssetIntegrityHashesTest extends TestCase
{
    /**
     * @var StoreAssetIntegrityHashes
     */
    private StoreAssetIntegrityHashes $plugin;

    /**
     * @var MockObject|SubresourceIntegrityCollector
     */
    private MockObject $integrityCollectorMock;

    /**
     * @var MockObject|SubresourceIntegrityRepositoryPool
     */
    private MockObject $repositoryPoolMock;

    /**
     * @var MockObject|LoggerInterface
     */
    private MockObject $loggerMock;

    protected function setUp(): void
    {
        $this->integrityCollectorMock = $this->createMock(SubresourceIntegrityCollector::class);
        $this->repositoryPoolMock = $this->createMock(SubresourceIntegrityRepositoryPool::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);

        $this->plugin = new StoreAssetIntegrityHashes(
            $this->integrityCollectorMock,
            $this->repositoryPoolMock,
            $this->loggerMock
        );
    }

    /**
     * Test context extraction from file path
     */
    #[DataProvider('filePathProvider')]
    public function testAfterDeployExtractsCorrectContext(
        string $filePath,
        string $expectedContext
    ): void {
        $subjectMock = $this->createMock(DeployStaticContent::class);

        $integrityMock = $this->createMock(SubresourceIntegrity::class);
        $integrityMock->method('getPath')->willReturn($filePath);

        $this->integrityCollectorMock->method('release')->willReturn([$integrityMock]);

        $repositoryMock = $this->createMock(SubresourceIntegrityRepository::class);
        $this->repositoryPoolMock->expects($this->once())
            ->method('get')
            ->with($expectedContext)
            ->willReturn($repositoryMock);

        $this->plugin->afterDeploy($subjectMock, null, []);
    }

    /**
     * Data provider for file paths
     *
     * @return array
     */
    public static function filePathProvider(): array
    {
        return [
            'luma_en_US_js' => [
                'frontend/Magento/luma/en_US/js/file.js',
                'frontend/Magento/luma/en_US'
            ],
            'luma_de_DE_nested' => [
                'frontend/Magento/luma/de_DE/Magento_Checkout/js/view/cart.js',
                'frontend/Magento/luma/de_DE'
            ],
            'blank_fr_FR' => [
                'frontend/Magento/blank/fr_FR/js/theme.js',
                'frontend/Magento/blank/fr_FR'
            ],
            'adminhtml_backend' => [
                'adminhtml/Magento/backend/en_US/js/admin.js',
                'adminhtml/Magento/backend/en_US'
            ],
            'custom_vendor_theme' => [
                'frontend/Vendor/mytheme/es_ES/js/custom.js',
                'frontend/Vendor/mytheme/es_ES'
            ],
            'deeply_nested' => [
                'frontend/Magento/luma/en_US/Magento_Checkout/js/view/payment/default.js',
                'frontend/Magento/luma/en_US'
            ],
        ];
    }

    /**
     * Test empty path is skipped
     */
    public function testAfterDeploySkipsEmptyPath(): void
    {
        $subjectMock = $this->createMock(DeployStaticContent::class);

        $integrityMock = $this->createMock(SubresourceIntegrity::class);
        $integrityMock->method('getPath')->willReturn('');

        $this->integrityCollectorMock->method('release')->willReturn([$integrityMock]);

        $this->repositoryPoolMock->expects($this->never())->method('get');

        $this->plugin->afterDeploy($subjectMock, null, []);
    }

    /**
     * Test path with fewer than 4 segments is skipped
     */
    #[DataProvider('invalidPathProvider')]
    public function testAfterDeploySkipsInvalidPaths(string $invalidPath): void
    {
        $subjectMock = $this->createMock(DeployStaticContent::class);

        $integrityMock = $this->createMock(SubresourceIntegrity::class);
        $integrityMock->method('getPath')->willReturn($invalidPath);

        $this->integrityCollectorMock->method('release')->willReturn([$integrityMock]);

        $this->repositoryPoolMock->expects($this->never())->method('get');

        $this->plugin->afterDeploy($subjectMock, null, []);
    }

    /**
     * Data provider for invalid paths
     *
     * @return array
     */
    public static function invalidPathProvider(): array
    {
        return [
            'single_segment' => ['frontend'],
            'two_segments' => ['frontend/Magento'],
            'three_segments' => ['frontend/Magento/luma'],
            'empty_string' => [''],
        ];
    }

    /**
     * Test special characters in locale codes
     */
    #[DataProvider('specialLocaleProvider')]
    public function testAfterDeployHandlesSpecialLocales(string $filePath, string $expectedContext): void
    {
        $subjectMock = $this->createMock(DeployStaticContent::class);

        $integrityMock = $this->createMock(SubresourceIntegrity::class);
        $integrityMock->method('getPath')->willReturn($filePath);

        $this->integrityCollectorMock->method('release')->willReturn([$integrityMock]);

        $repositoryMock = $this->createMock(SubresourceIntegrityRepository::class);
        $this->repositoryPoolMock->expects($this->once())
            ->method('get')
            ->with($expectedContext)
            ->willReturn($repositoryMock);

        $this->plugin->afterDeploy($subjectMock, null, []);
    }

    /**
     * Data provider for special locale codes
     *
     * @return array
     */
    public static function specialLocaleProvider(): array
    {
        return [
            'chinese_hans' => [
                'frontend/Magento/luma/zh_Hans_CN/js/file.js',
                'frontend/Magento/luma/zh_Hans_CN'
            ],
            'chinese_hant' => [
                'frontend/Magento/luma/zh_Hant_TW/js/file.js',
                'frontend/Magento/luma/zh_Hant_TW'
            ],
            'serbian_latin' => [
                'frontend/Magento/luma/sr_Latn_RS/js/file.js',
                'frontend/Magento/luma/sr_Latn_RS'
            ],
        ];
    }

    /**
     * Test grouping by context
     */
    public function testAfterDeployGroupsByContext(): void
    {
        $subjectMock = $this->createMock(DeployStaticContent::class);

        $integrity1 = $this->createMock(SubresourceIntegrity::class);
        $integrity1->method('getPath')->willReturn('frontend/Magento/luma/en_US/js/file1.js');

        $integrity2 = $this->createMock(SubresourceIntegrity::class);
        $integrity2->method('getPath')->willReturn('frontend/Magento/luma/en_US/js/file2.js');

        $integrity3 = $this->createMock(SubresourceIntegrity::class);
        $integrity3->method('getPath')->willReturn('frontend/Magento/luma/de_DE/js/file1.js');

        $this->integrityCollectorMock->method('release')
            ->willReturn([$integrity1, $integrity2, $integrity3]);

        $repositoryEnUS = $this->createMock(SubresourceIntegrityRepository::class);
        $repositoryEnUS->expects($this->once())
            ->method('saveBunch')
            ->with($this->callback(function ($bunch) {
                return count($bunch) === 2;
            }));

        $repositoryDeDE = $this->createMock(SubresourceIntegrityRepository::class);
        $repositoryDeDE->expects($this->once())
            ->method('saveBunch')
            ->with($this->callback(function ($bunch) {
                return count($bunch) === 1;
            }));

        $this->repositoryPoolMock->method('get')
            ->willReturnCallback(function ($context) use ($repositoryEnUS, $repositoryDeDE) {
                return $context === 'frontend/Magento/luma/en_US'
                    ? $repositoryEnUS
                    : $repositoryDeDE;
            });

        $this->plugin->afterDeploy($subjectMock, null, []);
    }

    /**
     * Test exception is logged but doesn't fail
     */
    public function testAfterDeployLogsErrorOnFailure(): void
    {
        $subjectMock = $this->createMock(DeployStaticContent::class);

        $integrityMock = $this->createMock(SubresourceIntegrity::class);
        $integrityMock->method('getPath')->willReturn('frontend/Magento/luma/en_US/js/file.js');

        $this->integrityCollectorMock->method('release')->willReturn([$integrityMock]);

        $this->repositoryPoolMock->method('get')
            ->willThrowException(new Exception('Save failed'));

        $this->loggerMock->expects($this->once())
            ->method('error')
            ->with($this->stringContains('Failed saving'));

        $this->plugin->afterDeploy($subjectMock, null, []);
    }

    /**
     * Test mixed valid and invalid paths
     */
    public function testAfterDeployProcessesOnlyValidPaths(): void
    {
        $subjectMock = $this->createMock(DeployStaticContent::class);

        $validIntegrity = $this->createMock(SubresourceIntegrity::class);
        $validIntegrity->method('getPath')->willReturn('frontend/Magento/luma/en_US/js/valid.js');

        $invalidIntegrity1 = $this->createMock(SubresourceIntegrity::class);
        $invalidIntegrity1->method('getPath')->willReturn('');

        $invalidIntegrity2 = $this->createMock(SubresourceIntegrity::class);
        $invalidIntegrity2->method('getPath')->willReturn('only/two/segments');

        $this->integrityCollectorMock->method('release')
            ->willReturn([$validIntegrity, $invalidIntegrity1, $invalidIntegrity2]);

        $repositoryMock = $this->createMock(SubresourceIntegrityRepository::class);
        $repositoryMock->expects($this->once())
            ->method('saveBunch')
            ->with($this->callback(function ($bunch) {
                return count($bunch) === 1;
            }));

        $this->repositoryPoolMock->expects($this->once())
            ->method('get')
            ->with('frontend/Magento/luma/en_US')
            ->willReturn($repositoryMock);

        $this->plugin->afterDeploy($subjectMock, null, []);
    }

    /**
     * Test empty collector
     */
    public function testAfterDeployWithEmptyCollector(): void
    {
        $subjectMock = $this->createMock(DeployStaticContent::class);

        $this->integrityCollectorMock->method('release')->willReturn([]);
        $this->repositoryPoolMock->expects($this->never())->method('get');

        $this->plugin->afterDeploy($subjectMock, null, []);
    }

    /**
     * Test multiple themes in same deploy
     */
    public function testAfterDeployHandlesMultipleThemes(): void
    {
        $subjectMock = $this->createMock(DeployStaticContent::class);

        $lumaIntegrity = $this->createMock(SubresourceIntegrity::class);
        $lumaIntegrity->method('getPath')->willReturn('frontend/Magento/luma/en_US/js/file.js');

        $blankIntegrity = $this->createMock(SubresourceIntegrity::class);
        $blankIntegrity->method('getPath')->willReturn('frontend/Magento/blank/en_US/js/file.js');

        $this->integrityCollectorMock->method('release')
            ->willReturn([$lumaIntegrity, $blankIntegrity]);

        $lumaRepository = $this->createMock(SubresourceIntegrityRepository::class);
        $blankRepository = $this->createMock(SubresourceIntegrityRepository::class);

        $this->repositoryPoolMock->expects($this->exactly(2))
            ->method('get')
            ->willReturnCallback(function ($context) use ($lumaRepository, $blankRepository) {
                return $context === 'frontend/Magento/luma/en_US'
                    ? $lumaRepository
                    : $blankRepository;
            });

        $this->plugin->afterDeploy($subjectMock, null, []);
    }
}
