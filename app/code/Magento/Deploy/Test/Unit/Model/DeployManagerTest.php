<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Test\Unit\Model;

use Magento\Deploy\Console\Command\DeployStaticOptionsInterface as Options;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DeployManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Deploy\Model\DeployStrategyProviderFactory
     */
    private $deployStrategyProviderFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Symfony\Component\Console\Output\OutputInterface
     */
    private $outputMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\View\Deployment\Version\StorageInterface
     */
    private $versionStorageMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Deploy\Model\Deploy\TemplateMinifier
     */
    private $minifierTemplateMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Deploy\Model\ProcessQueueManagerFactory
     */
    private $processQueueManagerFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\State
     */
    private $stateMock;

    protected function setUp()
    {
        $this->deployStrategyProviderFactoryMock = $this->getMock(
            \Magento\Deploy\Model\DeployStrategyProviderFactory::class,
            ['create'],
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
        $this->minifierTemplateMock = $this->getMock(
            \Magento\Deploy\Model\Deploy\TemplateMinifier::class,
            [],
            [],
            '',
            false
        );
        $this->processQueueManagerFactoryMock = $this->getMock(
            \Magento\Deploy\Model\ProcessQueueManagerFactory::class,
            [],
            [],
            '',
            false
        );
        $this->stateMock = $this->getMockBuilder(\Magento\Framework\App\State::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->outputMock = $this->getMock(\Symfony\Component\Console\Output\OutputInterface::class, [], [], '', false);
    }

    public function testSaveDeployedVersion()
    {
        $version = (new \DateTime())->getTimestamp();
        $this->outputMock->expects($this->once())->method('writeln')->with("New version of deployed files: {$version}");
        $this->versionStorageMock->expects($this->once())->method('save')->with($version);

        $this->assertEquals(
            \Magento\Framework\Console\Cli::RETURN_SUCCESS,
            $this->getModel([Options::NO_HTML_MINIFY => true])->deploy()
        );
    }

    public function testSaveDeployedVersionDryRun()
    {
        $options = [Options::DRY_RUN => true, Options::NO_HTML_MINIFY => true];

        $this->outputMock->expects(self::once())->method('writeln')->with(
            'Dry run. Nothing will be recorded to the target directory.'
        );
        $this->versionStorageMock->expects($this->never())->method('save');

        $this->getModel($options)->deploy();
    }

    public function testMinifyTemplates()
    {
        $this->minifierTemplateMock->expects($this->once())->method('minifyTemplates')->willReturn(2);
        $this->outputMock->expects($this->atLeastOnce())->method('writeln')->withConsecutive(
            ["=== Minify templates ==="],
            ["\nSuccessful: 2 files modified\n---\n"]
        );

        $this->getModel([Options::NO_HTML_MINIFY => false])->deploy();
    }

    public function testMinifyTemplatesNoHtmlMinify()
    {
        $version = (new \DateTime())->getTimestamp();
        $this->outputMock->expects($this->once())->method('writeln')->with("New version of deployed files: {$version}");
        $this->versionStorageMock->expects($this->once())->method('save')->with($version);

        $this->getModel([Options::NO_HTML_MINIFY => true])->deploy();
    }

    public function testDeploy()
    {
        $area = 'frontend';
        $themePath = 'themepath';
        $locale = 'en_US';
        $options = [Options::NO_HTML_MINIFY => true];
        $strategyProviderMock = $this->getMock(\Magento\Deploy\Model\DeployStrategyProvider::class, [], [], '', false);
        $deployStrategyMock = $this->getMock(\Magento\Deploy\Model\Deploy\DeployInterface::class, [], [], '', false);

        $model = $this->getModel($options);
        $model->addPack($area, $themePath, $locale);
        $this->deployStrategyProviderFactoryMock->expects($this->once())->method('create')->with(
            ['output' => $this->outputMock, 'options' => $options]
        )->willReturn($strategyProviderMock);
        $strategyProviderMock->expects($this->once())->method('getDeployStrategies')->with($area, $themePath, [$locale])
            ->willReturn([$locale => $deployStrategyMock]);
        $this->stateMock->expects(self::once())->method('emulateAreaCode')
            ->with($area, [$deployStrategyMock, 'deploy'], [$area, $themePath, $locale])
            ->willReturn(\Magento\Framework\Console\Cli::RETURN_SUCCESS);

        $version = (new \DateTime())->getTimestamp();
        $this->outputMock->expects(self::once())->method('writeln')->with("New version of deployed files: {$version}");
        $this->versionStorageMock->expects($this->once())->method('save')->with($version);

        $this->assertEquals(\Magento\Framework\Console\Cli::RETURN_SUCCESS, $model->deploy());
    }

    /**
     * @param array $options
     * @return \Magento\Deploy\Model\DeployManager
     */
    private function getModel(array $options)
    {
        return new \Magento\Deploy\Model\DeployManager(
            $this->outputMock,
            $this->versionStorageMock,
            $this->deployStrategyProviderFactoryMock,
            $this->processQueueManagerFactoryMock,
            $this->minifierTemplateMock,
            $this->stateMock,
            $options
        );
    }
}
