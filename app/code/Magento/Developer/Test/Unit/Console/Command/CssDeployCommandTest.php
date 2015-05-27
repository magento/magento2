<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Developer\Test\Unit\Console\Command;

use Magento\Framework\Filesystem;
use Magento\Framework\View\Asset\Source;
use Magento\Framework\App\State;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\App\ObjectManager\ConfigLoader;
use Magento\Framework\View\Asset\SourceFileGeneratorPool;
use Magento\Framework\View\Asset\PreProcessor\ChainFactoryInterface;
use Magento\Developer\Console\Command\CssDeployCommand;
use Symfony\Component\Console\Tester\CommandTester;
use Magento\Framework\Validator\Locale;

class CssDeployCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CssDeployCommand
     */
    private $command;

    /**
     * @var ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManager;

    /**
     * @var Repository|\PHPUnit_Framework_MockObject_MockObject
     */
    private $assetRepo;

    /**
     * @var ConfigLoader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configLoader;

    /**
     * @var State|\PHPUnit_Framework_MockObject_MockObject
     */
    private $state;

    /**
     * @var SourceFileGeneratorPool|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sourceFileGeneratorPool;

    /**
     * @var Source|\PHPUnit_Framework_MockObject_MockObject
     */
    private $assetSource;

    /**
     * @var ChainFactoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $chainFactory;

    /**
     * @var Filesystem|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filesystem;

    /**
     * @var Locale|\PHPUnit_Framework_MockObject_MockObject
     */
    private $validator;

    public function setUp()
    {
        $this->objectManager = $this->getMockForAbstractClass('Magento\Framework\ObjectManagerInterface');
        $this->assetRepo = $this->getMock('Magento\Framework\View\Asset\Repository', [], [], '', false);
        $this->configLoader = $this->getMock('Magento\Framework\App\ObjectManager\ConfigLoader', [], [], '', false);
        $this->state = $this->getMock('Magento\Framework\App\State', [], [], '', false);
        $this->assetSource = $this->getMock('Magento\Framework\View\Asset\Source', [], [], '', false);
        $this->sourceFileGeneratorPool = $this->getMock(
            'Magento\Framework\View\Asset\SourceFileGeneratorPool',
            [],
            [],
            '',
            false
        );
        $this->chainFactory = $this->getMockForAbstractClass(
            'Magento\Framework\View\Asset\PreProcessor\ChainFactoryInterface'
        );
        $this->filesystem = $this->getMock('Magento\Framework\Filesystem', [], [], '', false);
        $this->validator = $this->getMock('Magento\Framework\Validator\Locale', [], [], '', false);

        $this->command = new CssDeployCommand(
            $this->objectManager,
            $this->assetRepo,
            $this->configLoader,
            $this->state,
            $this->assetSource,
            $this->sourceFileGeneratorPool,
            $this->chainFactory,
            $this->filesystem,
            $this->validator
        );
    }

    public function testExecute()
    {
        $file = 'css/styles-m' . '.less';

        $this->configLoader->expects($this->once())->method('load')->with('frontend')->willReturn([]);
        $this->objectManager->expects($this->once())->method('configure');
        $this->sourceFileGeneratorPool->expects($this->once())
            ->method('create')
            ->with('less')
            ->willReturn($this->getMock('Magento\Framework\Less\FileGenerator', [], [], '', false));
        $this->assetRepo->expects($this->once())
            ->method('createAsset')
            ->with(
                $file,
                [
                    'area' => 'frontend',
                    'theme' => 'Magento/blank',
                    'locale' => 'en_US'
                ]
            )
            ->willReturn(
                $this->getMockForAbstractClass('Magento\Framework\View\Asset\LocalInterface')
            );
        $this->assetSource->expects($this->once())->method('findSource')->willReturn('/dev/null');

        $this->chainFactory->expects($this->once())->method('create')->willReturn(
            $this->getMock('Magento\Framework\View\Asset\PreProcessor\Chain', [], [], '', false)
        );

        $this->filesystem->expects($this->atLeastOnce())->method('getDirectoryWrite')->willReturn(
            $this->getMock('\Magento\Framework\Filesystem\Directory\WriteInterface', [], [], '', false)
        );

        $this->validator->expects($this->once())->method('isValid')->with('en_US')->willReturn(true);

        $commandTester = new CommandTester($this->command);
        $commandTester->execute(
            [
                'type' => 'less'
            ]
        );
        $this->assertContains('Successfully processed LESS and/or SASS files', $commandTester->getDisplay());
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Not enough arguments
     */
    public function testExecuteWithoutParameters()
    {
        $commandTester = new CommandTester($this->command);
        $commandTester->execute([]);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage WRONG_LOCALE argument has invalid value, please run info:language:list
     */
    public function testExecuteWithWrongFormat()
    {
        $commandTester = new CommandTester($this->command);
        $commandTester->execute(
            [
                'type' => 'less',
                '--locale' => 'WRONG_LOCALE'
            ]
        );
    }
}
