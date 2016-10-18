<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Test\Unit\Model\Deploy;

use Magento\Deploy\Model\Deploy\LocaleDeploy;
use Magento\Framework\App\Utility\Files;
use Magento\Framework\App\View\Asset\Publisher;
use Magento\Framework\Translate\Js\Config;
use Magento\Framework\View\Asset\Minification;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\View\Asset\RepositoryFactory;
use Magento\RequireJs\Model\FileManagerFactory;
use Magento\Framework\RequireJs\ConfigFactory;
use Magento\Framework\View\Asset\Bundle\Manager;
use Magento\Framework\View\Design\Theme\ThemeProviderInterface;
use Magento\Framework\View\DesignInterfaceFactory;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\View\Design\Theme\ListInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Test class which allows deploy by locales
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class LocaleDeployTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    private $area;

    /**
     * @var string
     */
    private $locale;

    /**
     * @var string
     */
    private $themePath;

    /**
     * @var \Magento\Deploy\Model\Deploy\LocaleDeploy
     */
    private $model;

    protected function setUp()
    {
        $this->area = 'adminhtml';
        $this->themePath = '/theme/path';
        $this->locale = 'en_US';

        $outputMock = $this->getMock(OutputInterface::class, [], [], '', false);
        $jsTranslationMock = $this->getMock(Config::class, [], [], '', false);
        $jsTranslationMock->expects($this->once())->method('dictionaryEnabled')->willReturn(false);
        $minificationMock = $this->getMock(Minification::class, [], [], '', false);
        $minificationMock->expects($this->once())->method('isEnabled')->with('js')->willReturn(true);

        $themeMock = $this->getMockBuilder(\Magento\Framework\View\Design\ThemeInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $designMock = $this->getMock(\Magento\Framework\View\DesignInterface::class, [], [], '', false);
        $designMock->expects($this->once())->method('setDesignTheme')->with($themeMock, $this->area)->willReturnSelf();
        $assetRepoMock = $this->getMock(Repository::class, [], [], '', false);
        $assetRepoFactoryMock = $this->getMock(RepositoryFactory::class, ['create'], [], '', false);
        $assetRepoFactoryMock->expects($this->once())
            ->method('create')
            ->with(['design' => $designMock])
            ->willReturn($assetRepoMock);

        $fileManagerMock = $this->getMock(\Magento\RequireJs\Model\FileManager::class, [], [], '', false);
        $fileManagerMock->expects($this->once())->method('createRequireJsConfigAsset')->willReturnSelf();
        $fileManagerMock->expects($this->once())->method('createMinResolverAsset')->willReturnSelf();
        $fileManagerFactoryMock = $this->getMock(FileManagerFactory::class, ['create'], [], '', false);
        $fileManagerFactoryMock->expects($this->once())->method('create')->willReturn($fileManagerMock);

        $requireJsConfigMock = $this->getMock(\Magento\Framework\RequireJs\Config::class, [], [], '', false);
        $configFactoryMock = $this->getMock(ConfigFactory::class, ['create'], [], '', false);
        $configFactoryMock->expects($this->once())->method('create')->willReturn($requireJsConfigMock);

        $assetPublisherMock = $this->getMock(Publisher::class, [], [], '', false);

        $bundleManagerMock = $this->getMock(Manager::class, [], [], '', false);
        $bundleManagerMock->expects($this->once())->method('flush');

        $themeProviderMock = $this->getMock(ThemeProviderInterface::class, [], [], '', false);
        $loggerMock = $this->getMock(LoggerInterface::class, [], [], '', false);

        $filesUtilMock = $this->getMock(Files::class, [], [], '', false);
        $filesUtilMock->expects($this->once())->method('getStaticPreProcessingFiles')->willReturn([]);
        $filesUtilMock->expects($this->once())->method('getStaticLibraryFiles')->willReturn([]);

        $designFactoryMock = $this->getMock(DesignInterfaceFactory::class, ['create'], [], '', false);
        $designFactoryMock->expects($this->once())->method('create')->willReturn($designMock);

        $localeResolverMock = $this->getMock(ResolverInterface::class, [], [], '', false);
        $localeResolverMock->expects($this->once())->method('setLocale')->with($this->locale);

        $themeList = $this->getMock(ListInterface::class, [], [], '', false);
        $themeList->expects($this->once())->method('getThemeByFullPath')
            ->with($this->area . '/' . $this->themePath)
            ->willReturn($themeMock);

        $this->model = new LocaleDeploy(
            $outputMock,
            $jsTranslationMock,
            $minificationMock,
            $assetRepoMock,
            $assetRepoFactoryMock,
            $fileManagerFactoryMock,
            $configFactoryMock,
            $assetPublisherMock,
            $bundleManagerMock,
            $themeProviderMock,
            $loggerMock,
            $filesUtilMock,
            $designFactoryMock,
            $localeResolverMock,
            [],
            [\Magento\Deploy\Console\Command\DeployStaticOptionsInterface::NO_JAVASCRIPT => 0]
        );
        $property = new \ReflectionProperty(get_class($this->model), 'themeList');
        $property->setAccessible(true);
        $property->setValue($this->model, $themeList);
    }

    public function testDeploy()
    {
        $this->assertEquals(
            \Magento\Framework\Console\Cli::RETURN_SUCCESS,
            $this->model->deploy($this->area, $this->themePath, $this->locale)
        );
    }
}
