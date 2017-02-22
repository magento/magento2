<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Test\Unit\Model\Deploy;

use Magento\Framework\App\Utility\Files;
use Magento\Framework\App\View\Asset\Publisher;
use Magento\Framework\Translate\Js\Config;
use Magento\Framework\View\Asset\Minification;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\View\Asset\RepositoryFactory;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class LocaleDeployTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Config
     */
    private $jsTranslationMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Minification
     */
    private $minificationMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|RepositoryFactory
     */
    private $assetRepoFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\RequireJs\Model\FileManagerFactory
     */
    private $fileManagerFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\RequireJs\ConfigFactory
     */
    private $configFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\View\Asset\Bundle\Manager
     */
    private $bundleManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Files
     */
    private $filesUtilMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\View\DesignInterfaceFactory
     */
    private $designFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Locale\ResolverInterface
     */
    private $localeResolverMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|OutputInterface
     */
    private $outputMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|LoggerInterface
     */
    private $loggerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $assetRepoMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $assetPublisherMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $themeProviderMock;

    protected function setUp()
    {
        $this->outputMock = $this->getMock(OutputInterface::class, [], [], '', false);
        $this->loggerMock = $this->getMock(LoggerInterface::class, [], [], '', false);
        $this->filesUtilMock = $this->getMock(Files::class, [], [], '', false);
        $this->assetRepoMock = $this->getMock(Repository::class, [], [], '', false);
        $this->minificationMock = $this->getMock(Minification::class, [], [], '', false);
        $this->jsTranslationMock = $this->getMock(Config::class, [], [], '', false);
        $this->assetPublisherMock = $this->getMock(Publisher::class, [], [], '', false);
        $this->assetRepoFactoryMock = $this->getMock(
            RepositoryFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->fileManagerFactoryMock = $this->getMock(
            \Magento\RequireJs\Model\FileManagerFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->configFactoryMock = $this->getMock(
            \Magento\Framework\RequireJs\ConfigFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->bundleManagerMock = $this->getMock(
            \Magento\Framework\View\Asset\Bundle\Manager::class,
            [],
            [],
            '',
            false
        );
        $this->themeProviderMock = $this->getMock(
            \Magento\Framework\View\Design\Theme\ThemeProviderInterface::class,
            [],
            [],
            '',
            false
        );
        $this->designFactoryMock = $this->getMock(
            \Magento\Framework\View\DesignInterfaceFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->localeResolverMock = $this->getMock(
            \Magento\Framework\Locale\ResolverInterface::class,
            [],
            [],
            '',
            false
        );
    }

    public function testDeploy()
    {
        $area = 'adminhtml';
        $themePath = '/theme/path';
        $locale = 'en_US';

        $designMock = $this->getMock(\Magento\Framework\View\DesignInterface::class, [], [], '', false);
        $assetRepoMock = $this->getMock(Repository::class, [], [], '', false);
        $requireJsConfigMock = $this->getMock(\Magento\Framework\RequireJs\Config::class, [], [], '', false);
        $fileManagerMock = $this->getMock(\Magento\RequireJs\Model\FileManager::class, [], [], '', false);

        $model = $this->getModel([\Magento\Deploy\Console\Command\DeployStaticOptionsInterface::NO_JAVASCRIPT => 0]);

        $this->localeResolverMock->expects($this->once())->method('setLocale')->with($locale);
        $this->designFactoryMock->expects($this->once())->method('create')->willReturn($designMock);
        $designMock->expects($this->once())->method('setDesignTheme')->with($themePath, $area)->willReturnSelf();
        $this->assetRepoFactoryMock->expects($this->once())->method('create')->with(['design' => $designMock])
            ->willReturn($assetRepoMock);
        $this->configFactoryMock->expects($this->once())->method('create')->willReturn($requireJsConfigMock);
        $this->fileManagerFactoryMock->expects($this->once())->method('create')->willReturn($fileManagerMock);

        $fileManagerMock->expects($this->once())->method('createRequireJsConfigAsset')->willReturnSelf();
        $this->filesUtilMock->expects($this->once())->method('getStaticPreProcessingFiles')->willReturn([]);
        $this->filesUtilMock->expects($this->once())->method('getStaticLibraryFiles')->willReturn([]);

        $this->jsTranslationMock->expects($this->once())->method('dictionaryEnabled')->willReturn(false);
        $this->minificationMock->expects($this->once())->method('isEnabled')->with('js')->willReturn(true);
        $fileManagerMock->expects($this->once())->method('createMinResolverAsset')->willReturnSelf();

        $this->bundleManagerMock->expects($this->once())->method('flush');

        $this->assertEquals(
            \Magento\Framework\Console\Cli::RETURN_SUCCESS,
            $model->deploy($area, $themePath, $locale)
        );
    }

    /**
     * @param array $options
     * @return \Magento\Deploy\Model\Deploy\LocaleDeploy
     */
    private function getModel($options = [])
    {
        return new \Magento\Deploy\Model\Deploy\LocaleDeploy(
            $this->outputMock,
            $this->jsTranslationMock,
            $this->minificationMock,
            $this->assetRepoMock,
            $this->assetRepoFactoryMock,
            $this->fileManagerFactoryMock,
            $this->configFactoryMock,
            $this->assetPublisherMock,
            $this->bundleManagerMock,
            $this->themeProviderMock,
            $this->loggerMock,
            $this->filesUtilMock,
            $this->designFactoryMock,
            $this->localeResolverMock,
            [],
            $options
        );
    }
}
