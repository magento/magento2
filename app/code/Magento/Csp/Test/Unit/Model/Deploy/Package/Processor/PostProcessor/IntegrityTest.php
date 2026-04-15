<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Csp\Test\Unit\Model\Deploy\Package\Processor\PostProcessor;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\Attributes\DataProvider;
use Magento\Csp\Model\Deploy\Package\Processor\PostProcessor\Integrity;
use Magento\Csp\Model\SubresourceIntegrityCollector;
use Magento\Csp\Model\SubresourceIntegrityRepositoryPool;
use Magento\Csp\Model\SubresourceIntegrityRepository;
use Magento\Csp\Model\SubresourceIntegrity;
use Magento\Csp\Model\SubresourceIntegrityFactory;
use Magento\Csp\Model\SubresourceIntegrity\HashGenerator;
use Magento\Deploy\Package\Package;
use Magento\Deploy\Package\PackageFile;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\View\Asset\Minification;

/**
 * Unit tests for Integrity post-processor
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class IntegrityTest extends TestCase
{
    /**
     * @var Integrity
     */
    private Integrity $processor;

    /**
     * @var MockObject|Filesystem
     */
    private MockObject $filesystemMock;

    /**
     * @var MockObject|HashGenerator
     */
    private MockObject $hashGeneratorMock;

    /**
     * @var MockObject|SubresourceIntegrityFactory
     */
    private MockObject $integrityFactoryMock;

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

    /**
     * @var MockObject|ReadInterface
     */
    private MockObject $directoryMock;

    /**
     * @var MockObject|Minification
     */
    private MockObject $minificationMock;

    protected function setUp(): void
    {
        $this->filesystemMock = $this->createMock(Filesystem::class);
        $this->hashGeneratorMock = $this->createMock(HashGenerator::class);
        $this->integrityFactoryMock = $this->createMock(SubresourceIntegrityFactory::class);
        $this->integrityCollectorMock = $this->createMock(SubresourceIntegrityCollector::class);
        $this->repositoryPoolMock = $this->createMock(SubresourceIntegrityRepositoryPool::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->directoryMock = $this->createMock(ReadInterface::class);
        $this->minificationMock = $this->createMock(Minification::class);

        $this->filesystemMock->method('getDirectoryRead')->willReturn($this->directoryMock);

        $this->processor = new Integrity(
            $this->filesystemMock,
            $this->hashGeneratorMock,
            $this->integrityFactoryMock,
            $this->integrityCollectorMock,
            $this->loggerMock,
            $this->repositoryPoolMock,
            $this->minificationMock
        );
    }

    /**
     * Test that context is the full package path (area/theme/locale)
     */
    public function testProcessUsesFullPackagePathAsContext(): void
    {
        $expectedContext = 'frontend/Magento/luma/en_US';

        $packageMock = $this->createMock(Package::class);
        $packageMock->expects($this->once())
            ->method('getPath')
            ->willReturn($expectedContext);
        $packageMock->method('getFiles')->willReturn([]);

        $integrityMock = $this->createMock(SubresourceIntegrity::class);
        $this->integrityCollectorMock->method('release')->willReturn([$integrityMock]);

        $repositoryMock = $this->createMock(SubresourceIntegrityRepository::class);
        $repositoryMock->expects($this->once())->method('saveBunch');

        $this->repositoryPoolMock->expects($this->once())
            ->method('get')
            ->with($expectedContext)
            ->willReturn($repositoryMock);

        $this->processor->process($packageMock, []);
    }

    /**
     * Test with different theme/locale combinations
     */
    #[DataProvider('packagePathProvider')]
    public function testProcessWithVariousPackagePaths(string $packagePath): void
    {
        $packageMock = $this->createMock(Package::class);
        $packageMock->method('getPath')->willReturn($packagePath);
        $packageMock->method('getFiles')->willReturn([]);

        $integrityMock = $this->createMock(SubresourceIntegrity::class);
        $this->integrityCollectorMock->method('release')->willReturn([$integrityMock]);

        $repositoryMock = $this->createMock(SubresourceIntegrityRepository::class);
        $this->repositoryPoolMock->expects($this->once())
            ->method('get')
            ->with($packagePath)
            ->willReturn($repositoryMock);

        $this->processor->process($packageMock, []);
    }

    /**
     * Data provider for package paths
     *
     * @return array
     */
    public static function packagePathProvider(): array
    {
        return [
            'luma_en_US' => ['frontend/Magento/luma/en_US'],
            'luma_de_DE' => ['frontend/Magento/luma/de_DE'],
            'blank_en_US' => ['frontend/Magento/blank/en_US'],
            'adminhtml_backend' => ['adminhtml/Magento/backend/en_US'],
            'custom_theme' => ['frontend/Vendor/custom/fr_FR'],
            'arabic_locale' => ['frontend/Magento/luma/ar_SA'],
            'chinese_locale' => ['frontend/Magento/luma/zh_Hans_CN'],
        ];
    }

    /**
     * Test that empty collected data doesn't trigger save
     */
    public function testProcessWithNoCollectedData(): void
    {
        $packageMock = $this->createMock(Package::class);
        $packageMock->method('getFiles')->willReturn([]);

        $this->integrityCollectorMock->method('release')->willReturn([]);

        $this->repositoryPoolMock->expects($this->never())->method('get');

        $result = $this->processor->process($packageMock, []);
        $this->assertTrue($result);
    }

    /**
     * Test exception handling during save
     */
    public function testProcessLogsErrorOnSaveFailure(): void
    {
        $packageMock = $this->createMock(Package::class);
        $packageMock->method('getPath')->willReturn('frontend/Magento/luma/en_US');
        $packageMock->method('getFiles')->willReturn([]);

        $integrityMock = $this->createMock(SubresourceIntegrity::class);
        $this->integrityCollectorMock->method('release')->willReturn([$integrityMock]);

        $this->repositoryPoolMock->method('get')
            ->willThrowException(new \Exception('Save failed'));

        $this->loggerMock->expects($this->once())
            ->method('error')
            ->with($this->stringContains('Failed saving'));

        $result = $this->processor->process($packageMock, []);
        $this->assertTrue($result);
    }

    /**
     * Test collector is cleared after processing
     */
    public function testProcessClearsCollector(): void
    {
        $packageMock = $this->createMock(Package::class);
        $packageMock->method('getPath')->willReturn('frontend/Magento/luma/en_US');
        $packageMock->method('getFiles')->willReturn([]);

        $integrityMock = $this->createMock(SubresourceIntegrity::class);
        $this->integrityCollectorMock->method('release')->willReturn([$integrityMock]);
        $this->integrityCollectorMock->expects($this->once())->method('clear');

        $repositoryMock = $this->createMock(SubresourceIntegrityRepository::class);
        $this->repositoryPoolMock->method('get')->willReturn($repositoryMock);

        $this->processor->process($packageMock, []);
    }

    /**
     * Test handling of base area packages
     */
    public function testProcessHandlesBaseAreaPackage(): void
    {
        $packageMock = $this->createMock(Package::class);
        $packageMock->method('getPath')->willReturn('base');
        $packageMock->method('getFiles')->willReturn([]);

        $integrityMock = $this->createMock(SubresourceIntegrity::class);
        $this->integrityCollectorMock->method('release')->willReturn([$integrityMock]);

        $repositoryMock = $this->createMock(SubresourceIntegrityRepository::class);
        $this->repositoryPoolMock->expects($this->once())
            ->method('get')
            ->with('base')
            ->willReturn($repositoryMock);

        $result = $this->processor->process($packageMock, []);
        $this->assertTrue($result);
    }

    /**
     * Test with very long theme path
     */
    public function testProcessWithLongThemePath(): void
    {
        $longPath = 'frontend/VeryLongVendorNameThatExceedsNormalLength/extremely_long_theme_name_here/en_US';

        $packageMock = $this->createMock(Package::class);
        $packageMock->method('getPath')->willReturn($longPath);
        $packageMock->method('getFiles')->willReturn([]);

        $integrityMock = $this->createMock(SubresourceIntegrity::class);
        $this->integrityCollectorMock->method('release')->willReturn([$integrityMock]);

        $repositoryMock = $this->createMock(SubresourceIntegrityRepository::class);
        $this->repositoryPoolMock->expects($this->once())
            ->method('get')
            ->with($longPath)
            ->willReturn($repositoryMock);

        $this->processor->process($packageMock, []);
    }

    /**
     * Test JS files are processed and hashes generated
     */
    public function testProcessGeneratesHashesForJsFiles(): void
    {
        $packageMock = $this->createMock(Package::class);
        $packageMock->method('getPath')->willReturn('frontend/Magento/luma/en_US');

        $jsFileMock = $this->createMock(PackageFile::class);
        $jsFileMock->method('getExtension')->willReturn('js');
        $jsFileMock->method('getSourcePath')->willReturn('/path/to/file.js');
        $jsFileMock->method('getDeployedFilePath')->willReturn('frontend/Magento/luma/en_US/js/file.js');

        $packageMock->method('getFiles')->willReturn([$jsFileMock]);

        $this->directoryMock->method('readFile')->willReturn('console.log("test");');
        $this->hashGeneratorMock->method('generate')->willReturn('sha256-testhash');

        $integrityMock = $this->createMock(SubresourceIntegrity::class);
        $this->integrityFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($integrityMock);

        $this->integrityCollectorMock->expects($this->once())
            ->method('collect')
            ->with($integrityMock);

        $this->integrityCollectorMock->method('release')->willReturn([$integrityMock]);

        $repositoryMock = $this->createMock(SubresourceIntegrityRepository::class);
        $this->repositoryPoolMock->method('get')->willReturn($repositoryMock);

        $this->processor->process($packageMock, []);
    }

    /**
     * Test non-JS files are skipped
     */
    public function testProcessSkipsNonJsFiles(): void
    {
        $packageMock = $this->createMock(Package::class);
        $packageMock->method('getPath')->willReturn('frontend/Magento/luma/en_US');

        $cssFileMock = $this->createMock(PackageFile::class);
        $cssFileMock->method('getExtension')->willReturn('css');

        $htmlFileMock = $this->createMock(PackageFile::class);
        $htmlFileMock->method('getExtension')->willReturn('html');

        $packageMock->method('getFiles')->willReturn([$cssFileMock, $htmlFileMock]);

        $this->integrityFactoryMock->expects($this->never())->method('create');
        $this->integrityCollectorMock->expects($this->never())->method('collect');
        $this->integrityCollectorMock->method('release')->willReturn([]);

        $this->processor->process($packageMock, []);
    }

    /**
     * Test process returns true on success
     */
    public function testProcessReturnsTrue(): void
    {
        $packageMock = $this->createMock(Package::class);
        $packageMock->method('getFiles')->willReturn([]);
        $this->integrityCollectorMock->method('release')->willReturn([]);

        $result = $this->processor->process($packageMock, []);

        $this->assertTrue($result);
    }
}
