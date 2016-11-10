<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Test\Unit\Console\Command;

use Magento\Setup\Console\Command\DeployStaticContentCommand;
use Symfony\Component\Console\Tester\CommandTester;
use Magento\Framework\Validator\Locale;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\App\State;

require 'FunctionExistMock.php';

class DeployStaticContentCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Deploy\Model\DeployManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $deployer;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManager;

    /**
     * @var \Magento\Framework\App\ObjectManagerFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManagerFactory;

    /**
     * @var \Magento\Framework\App\Utility\Files|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filesUtil;

    /**
     * @var DeployStaticContentCommand
     */
    private $command;

    /**
     * @var Locale|\PHPUnit_Framework_MockObject_MockObject
     */
    private $validator;

    /**
     * @var \Magento\Framework\App\State|\PHPUnit_Framework_MockObject_MockObject
     */
    private $appState;

    protected function setUp()
    {
        $this->objectManager = $this->getMockForAbstractClass(\Magento\Framework\ObjectManagerInterface::class);
        $this->objectManagerFactory = $this->getMock(
            \Magento\Framework\App\ObjectManagerFactory::class,
            [],
            [],
            '',
            false
        );
        $this->deployer = $this->getMock(\Magento\Deploy\Model\DeployManager::class, [], [], '', false);
        $this->filesUtil = $this->getMock(\Magento\Framework\App\Utility\Files::class, [], [], '', false);
        $this->appState = $this->getMock(\Magento\Framework\App\State::class, [], [], '', false);

        $this->validator = $this->getMock(\Magento\Framework\Validator\Locale::class, [], [], '', false);
        $this->command = (new ObjectManager($this))->getObject(DeployStaticContentCommand::class, [
            'objectManagerFactory' => $this->objectManagerFactory,
            'validator' => $this->validator,
            'objectManager' => $this->objectManager,
            'appState' => $this->appState,
        ]);
    }

    public function testExecute()
    {
        $this->appState->expects($this->once())->method('getMode')->willReturn(State::MODE_PRODUCTION);
        $this->filesUtil->expects(self::any())->method('getStaticPreProcessingFiles')->willReturn([]);
        $this->deployer->expects($this->once())->method('deploy');
        $this->objectManager->expects($this->at(0))->method('create')->willReturn($this->filesUtil);
        $this->objectManager->expects($this->at(1))->method('create')->willReturn($this->deployer);

        $tester = new CommandTester($this->command);
        $tester->execute([]);
    }

    public function testExecuteValidateLanguages()
    {
        $this->appState->expects($this->once())->method('getMode')->willReturn(State::MODE_PRODUCTION);
        $this->filesUtil->expects(self::any())->method('getStaticPreProcessingFiles')->willReturn([]);
        $this->deployer->expects($this->once())->method('deploy');
        $this->objectManager->expects($this->at(0))->method('create')->willReturn($this->filesUtil);
        $this->objectManager->expects($this->at(1))->method('create')->willReturn($this->deployer);
        $this->validator->expects(self::exactly(2))->method('isValid')->willReturnMap([
            ['en_US', true],
            ['uk_UA', true],
        ]);

        $tester = new CommandTester($this->command);
        $tester->execute(['languages' => ['en_US', 'uk_UA']]);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage --language (-l) and --exclude-language cannot be used at the same tim
     */
    public function testExecuteIncludedExcludedLanguages()
    {
        $this->appState->expects($this->once())->method('getMode')->willReturn(State::MODE_PRODUCTION);
        $this->filesUtil->expects(self::any())->method('getStaticPreProcessingFiles')->willReturn([]);
        $this->objectManager->expects($this->at(0))->method('create')->willReturn($this->filesUtil);
        $this->validator->expects(self::exactly(2))->method('isValid')->willReturnMap([
            ['en_US', true],
            ['uk_UA', true],
        ]);

        $tester = new CommandTester($this->command);
        $tester->execute(['--language' => ['en_US', 'uk_UA'], '--exclude-language' => 'ru_RU']);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage --area (-a) and --exclude-area cannot be used at the same tim
     */
    public function testExecuteIncludedExcludedAreas()
    {
        $this->appState->expects($this->once())->method('getMode')->willReturn(State::MODE_PRODUCTION);
        $this->filesUtil->expects(self::any())->method('getStaticPreProcessingFiles')->willReturn([]);
        $this->objectManager->expects($this->at(0))->method('create')->willReturn($this->filesUtil);

        $tester = new CommandTester($this->command);
        $tester->execute(['--area' => ['a1', 'a2'], '--exclude-area' => 'a3']);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage --theme (-t) and --exclude-theme cannot be used at the same tim
     */
    public function testExecuteIncludedExcludedThemes()
    {
        $this->appState->expects($this->once())->method('getMode')->willReturn(State::MODE_PRODUCTION);
        $this->filesUtil->expects(self::any())->method('getStaticPreProcessingFiles')->willReturn([]);
        $this->objectManager->expects($this->at(0))->method('create')->willReturn($this->filesUtil);

        $tester = new CommandTester($this->command);
        $tester->execute(['--theme' => ['t1', 't2'], '--exclude-theme' => 't3']);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage ARG_IS_WRONG argument has invalid value, please run info:language:list
     */
    public function testExecuteInvalidLanguageArgument()
    {
        $this->appState->expects($this->once())->method('getMode')->willReturn(State::MODE_PRODUCTION);
        $this->filesUtil->expects(self::any())->method('getStaticPreProcessingFiles')->willReturn([]);
        $this->objectManager->expects($this->at(0))
            ->method('create')
            ->willReturn($this->filesUtil);
        $wrongParam = ['languages' => ['ARG_IS_WRONG']];
        $commandTester = new CommandTester($this->command);
        $commandTester->execute($wrongParam);
    }

    /**
     * @param string $mode
     * @return void
     * @expectedException  \Magento\Framework\Exception\LocalizedException
     * @dataProvider executionInNonProductionModeDataProvider
     */
    public function testExecuteInNonProductionMode($mode)
    {
        $this->appState->expects($this->any())->method('getMode')->willReturn($mode);
        $this->objectManager->expects($this->never())->method('create');

        $tester = new CommandTester($this->command);
        $tester->execute([]);
    }

    /**
     * @return array
     */
    public function executionInNonProductionModeDataProvider()
    {
        return [
            [State::MODE_DEFAULT],
            [State::MODE_DEVELOPER],
        ];
    }
}
