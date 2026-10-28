<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Deploy\Test\Unit\Service;

use Magento\Deploy\Service\DeployRequireJsConfig;
use Magento\Framework\Locale\ResolverInterfaceFactory;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\View\Asset\ConfigInterface;
use Magento\RequireJs\Model\FileManagerFactory;
use Magento\Framework\View\DesignInterfaceFactory;
use Magento\Framework\View\Design\Theme\ListInterface;
use Magento\Framework\View\Asset\RepositoryFactory;
use Magento\Framework\RequireJs\ConfigFactory;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Theme\Model\View\Design;
use Magento\Framework\View\Asset\Repository;
use Magento\RequireJs\Model\FileManager;
use Magento\Framework\RequireJs\Config;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Unit test for DeployRequireJsConfig service.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DeployRequireJsConfigTest extends TestCase
{
    /**
     * @var DeployRequireJsConfig
     */
    private $service;

    /**
     * @var ListInterface|MockObject
     */
    private $themeList;

    /**
     * @var DesignInterfaceFactory|MockObject
     */
    private $designFactory;

    /**
     * @var RepositoryFactory|MockObject
     */
    private $assetRepoFactory;

    /**
     * @var FileManagerFactory|MockObject
     */
    private $fileManagerFactory;

    /**
     * @var ConfigFactory|MockObject
     */
    private $requireJsConfigFactory;

    /**
     * @var ResolverInterfaceFactory|MockObject
     */
    private $localeFactory;

    /**
     * @var ConfigInterface|MockObject
     */
    private $bundleConfig;

    /**
     * @var ThemeInterface|MockObject
     */
    private $theme;

    /**
     * @var Design|MockObject
     */
    private $design;

    /**
     * @var ResolverInterface|MockObject
     */
    private $locale;

    /**
     * @var Repository|MockObject
     */
    private $assetRepo;

    /**
     * @var FileManager|MockObject
     */
    private $fileManager;

    /**
     * @var Config|MockObject
     */
    private $requireJsConfig;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->themeList = $this->createMock(ListInterface::class);
        $this->designFactory = $this->createMock(DesignInterfaceFactory::class);
        $this->assetRepoFactory = $this->createMock(RepositoryFactory::class);
        $this->fileManagerFactory = $this->createMock(FileManagerFactory::class);
        $this->requireJsConfigFactory = $this->createMock(ConfigFactory::class);
        $this->localeFactory = $this->createMock(ResolverInterfaceFactory::class);
        $this->bundleConfig = $this->createMock(ConfigInterface::class);

        $this->theme = $this->createMock(ThemeInterface::class);
        $this->design = $this->createMock(Design::class);
        $this->locale = $this->createMock(ResolverInterface::class);
        $this->assetRepo = $this->createMock(Repository::class);
        $this->fileManager = $this->createMock(FileManager::class);
        $this->requireJsConfig = $this->createMock(Config::class);

        $this->service = new DeployRequireJsConfig(
            $this->themeList,
            $this->designFactory,
            $this->assetRepoFactory,
            $this->fileManagerFactory,
            $this->requireJsConfigFactory,
            $this->localeFactory,
            $this->bundleConfig
        );

        $this->setupCommonMocks();
    }

    /**
     * Test successful deployment with bundling enabled.
     */
    public function testDeployWithBundlingEnabled(): void
    {
        $areaCode = 'frontend';
        $themePath = 'Magento/luma';
        $localeCode = 'en_US';
        $fullThemePath = $areaCode . '/' . $themePath;

        $this->themeList->expects($this->once())
            ->method('getThemeByFullPath')
            ->with($fullThemePath)
            ->willReturn($this->theme);

        $this->setupBundlingMocks(true);
        $this->setupFileManagerMocks(true);

        $result = $this->service->deploy($areaCode, $themePath, $localeCode);

        $this->assertTrue($result);
    }

    /**
     * Test successful deployment with bundling disabled.
     */
    public function testDeployWithBundlingDisabled(): void
    {
        $areaCode = 'frontend';
        $themePath = 'Magento/luma';
        $localeCode = 'en_US';
        $fullThemePath = $areaCode . '/' . $themePath;

        $this->themeList->expects($this->once())
            ->method('getThemeByFullPath')
            ->with($fullThemePath)
            ->willReturn($this->theme);

        $this->setupBundlingMocks(false);
        $this->setupFileManagerMocks(false);

        $result = $this->service->deploy($areaCode, $themePath, $localeCode);

        $this->assertTrue($result);
    }

    /**
     * Test deployment with admin area.
     */
    public function testDeployWithAdminArea(): void
    {
        $areaCode = 'adminhtml';
        $themePath = 'Magento/backend';
        $localeCode = 'en_US';
        $fullThemePath = $areaCode . '/' . $themePath;

        $this->themeList->expects($this->once())
            ->method('getThemeByFullPath')
            ->with($fullThemePath)
            ->willReturn($this->theme);

        $this->setupBundlingMocks(true);
        $this->setupFileManagerMocks(true);

        $result = $this->service->deploy($areaCode, $themePath, $localeCode);

        $this->assertTrue($result);
    }

    /**
     * Test deployment with different locale.
     */
    public function testDeployWithDifferentLocale(): void
    {
        $areaCode = 'frontend';
        $themePath = 'Magento/luma';
        $localeCode = 'fr_FR';
        $fullThemePath = $areaCode . '/' . $themePath;

        $this->themeList->expects($this->once())
            ->method('getThemeByFullPath')
            ->with($fullThemePath)
            ->willReturn($this->theme);

        $this->setupBundlingMocks(false);
        $this->setupFileManagerMocks(false);

        $result = $this->service->deploy($areaCode, $themePath, $localeCode);

        $this->assertTrue($result);
    }

    /**
     * Test that the service returns true on successful deployment.
     */
    public function testDeployReturnsTrue(): void
    {
        $areaCode = 'frontend';
        $themePath = 'Magento/luma';
        $localeCode = 'en_US';
        $fullThemePath = $areaCode . '/' . $themePath;

        $this->themeList->expects($this->once())
            ->method('getThemeByFullPath')
            ->with($fullThemePath)
            ->willReturn($this->theme);

        $this->setupBundlingMocks(false);
        $this->setupFileManagerMocks(false);

        $result = $this->service->deploy($areaCode, $themePath, $localeCode);

        $this->assertTrue($result);
    }

    /**
     * Set up common mock expectations that are used across all tests.
     */
    private function setupCommonMocks(): void
    {
        $this->designFactory->method('create')->willReturn($this->design);
        $this->design->method('setDesignTheme')->willReturnSelf();
        $this->design->method('setLocale')->willReturnSelf();

        $this->localeFactory->method('create')->willReturn($this->locale);
        $this->locale->method('setLocale')->willReturnSelf();

        $this->assetRepoFactory->method('create')->willReturn($this->assetRepo);

        $this->requireJsConfigFactory->method('create')->willReturn($this->requireJsConfig);

        $this->fileManagerFactory->method('create')->willReturn($this->fileManager);
    }

    /**
     * Set up bundling configuration mocks.
     *
     * @param bool $bundlingEnabled
     */
    private function setupBundlingMocks(bool $bundlingEnabled): void
    {
        $this->bundleConfig->expects($this->once())
            ->method('isBundlingJsFiles')
            ->willReturn($bundlingEnabled);
    }

    /**
     * Set up file manager method call expectations.
     *
     * @param bool $bundlingEnabled
     */
    private function setupFileManagerMocks(bool $bundlingEnabled): void
    {
        if ($bundlingEnabled) {
            $this->fileManager->expects($this->once())->method('createStaticJsAsset');
            $this->fileManager->expects($this->once())->method('createRequireJsMixinsAsset');
        } else {
            $this->fileManager->expects($this->never())->method('createStaticJsAsset');
            $this->fileManager->expects($this->never())->method('createRequireJsMixinsAsset');
        }

        $this->fileManager->expects($this->once())->method('createRequireJsConfigAsset');
        $this->fileManager->expects($this->once())->method('createMinResolverAsset');
    }
}
