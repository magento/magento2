<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Test\Unit\Model;

class DeployManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Deploy\Model\DeployManager
     */
    private $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Deploy\Model\DeployStrategyProviderFactory
     */
    private $deployStrategyProviderFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Symfony\Component\Console\Output\OutputInterface
     */
    private $outputMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\View\Asset\ConfigInterface
     */
    private $assetConfigMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\Utility\Files
     */
    private $filesUtilsMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\View\Deployment\Version\StorageInterface
     */
    private $versionStorageMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\View\Template\Html\MinifierInterface
     */
    private $minifierMock;

    protected function setUp()
    {
        $this->deployStrategyProviderFactoryMock = $this->getMock(
            \Magento\Deploy\Model\DeployStrategyProviderFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->assetConfigMock = $this->getMock(
            \Magento\Framework\View\Asset\ConfigInterface::class,
            [],
            [],
            '',
            false
        );
        $this->versionStorageMock = $this->getMock(
            \Magento\Framework\App\View\Deployment\Version\StorageInterface::class,
            [],
            [],
            '',
            false
        );
        $this->minifierMock = $this->getMock(
            \Magento\Framework\View\Template\Html\MinifierInterface::class,
            [],
            [],
            '',
            false
        );
        $this->outputMock = $this->getMock(\Symfony\Component\Console\Output\OutputInterface::class, [], [], '', false);
        $this->filesUtilsMock = $this->getMock(\Magento\Framework\App\Utility\Files::class, [], [], '', false);

        $this->model = new \Magento\Deploy\Model\DeployManager(
            $this->outputMock,
            $this->assetConfigMock,
            $this->filesUtilsMock,
            $this->versionStorageMock,
            $this->minifierMock,
            $this->deployStrategyProviderFactoryMock,
            []
        );
    }

    public function testSaveDeployedVersion()
    {
        $version = (new \DateTime())->getTimestamp();

        $this->outputMock->expects($this->once())->method('writeln')->with("New version of deployed files: {$version}");
        $this->versionStorageMock->expects($this->never())->method('save');

        $this->model->saveDeployedVersion();
    }

    public function testSaveDeployedVersionDryRun()
    {
        $options = [\Magento\Deploy\Console\Command\DeployStaticOptionsInterface::DRY_RUN => false];
        $version = (new \DateTime())->getTimestamp();

        $this->outputMock->expects($this->once())->method('writeln')->with("New version of deployed files: {$version}");
        $this->versionStorageMock->expects($this->once())->method('save')->with($version);

        $this->getModel($options)->saveDeployedVersion();
    }

    public function testMinifyTemplates()
    {
        $templateMock = "template.phtml";
        $templatesMock = [$templateMock];

        $this->assetConfigMock->expects($this->once())->method('isMinifyHtml')->willReturn(true);
        $this->filesUtilsMock->expects($this->once())->method('getPhtmlFiles')->with(false, false)
            ->willReturn($templatesMock);
        $this->minifierMock->expects($this->once())->method('minify')->with($templateMock);
        $this->outputMock->expects($this->once())->method('getVerbosity')
            ->willReturn(\Symfony\Component\Console\Output\OutputInterface::VERBOSITY_VERBOSE);
        $this->outputMock->expects($this->atLeastOnce())->method('writeln')->withConsecutive(
            ["=== Minify templates ==="],
            [$templateMock . " minified\n"],
            ["\nSuccessful: " . count($templatesMock) . " files modified\n---\n"]
        );

        $this->model->minifyTemplates();
    }

    public function testMinifyTemplatesNoHtmlMinify()
    {
        $options = [\Magento\Deploy\Console\Command\DeployStaticOptionsInterface::NO_HTML_MINIFY => true];
        $this->outputMock->expects($this->never())->method('writeln');

        $this->getModel($options)->minifyTemplates();
    }

    public function testDeploy()
    {
        $area = 'frontend';
        $themePath = 'themepath';
        $locale = 'en_US';
        $options = [];
        $strategyProviderMock = $this->getMock(\Magento\Deploy\Model\DeployStrategyProvider::class, [], [], '', false);
        $deployStrategyMock = $this->getMock(\Magento\Deploy\Model\Deploy\DeployInterface::class, [], [], '', false);

        $this->model->addPack($area, $themePath, $locale);
        $this->deployStrategyProviderFactoryMock->expects($this->once())->method('create')->with(
            ['output' => $this->outputMock, 'options' => $options]
        )->willReturn($strategyProviderMock);
        $strategyProviderMock->expects($this->once())->method('getDeployStrategies')->with($area, $themePath, [$locale])
            ->willReturn([$deployStrategyMock]);
        $deployStrategyMock->expects($this->once())->method('deploy')
            ->willReturn(\Magento\Framework\Console\Cli::RETURN_SUCCESS);

        $this->assertEquals(\Magento\Framework\Console\Cli::RETURN_SUCCESS, $this->model->deploy());
    }

    /**
     * @param array $options
     * @return \Magento\Deploy\Model\DeployManager
     */
    private function getModel(array $options)
    {
        return new \Magento\Deploy\Model\DeployManager(
            $this->outputMock,
            $this->assetConfigMock,
            $this->filesUtilsMock,
            $this->versionStorageMock,
            $this->minifierMock,
            $this->deployStrategyProviderFactoryMock,
            $options
        );
    }
}
