<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Console\Command;

use Magento\Setup\Console\Command\DeployStaticContentCommand;
use Magento\Setup\Model\ObjectManagerProvider;

use Magento\Deploy\Console\ConsoleLogger;
use Magento\Deploy\Console\InputValidator;
use Magento\Deploy\Console\ConsoleLoggerFactory;
use Magento\Deploy\Console\DeployStaticOptions;
use Magento\Deploy\Service\DeployStaticContent;

use Magento\Framework\App\State;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

use Symfony\Component\Console\Tester\CommandTester;

use PHPUnit_Framework_MockObject_MockObject as Mock;

class DeployStaticContentCommandTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var DeployStaticContentCommand
     */
    private $command;

    /**
     * @var InputValidator|Mock
     */
    private $inputValidator;

    /**
     * @var ConsoleLogger|Mock
     */
    private $logger;

    /**
     * @var ConsoleLoggerFactory|Mock
     */
    private $consoleLoggerFactory;

    /**
     * @var DeployStaticContent|Mock
     */
    private $deployService;

    /**
     * Object manager to create various objects
     *
     * @var ObjectManagerInterface|Mock
     *
     */
    private $objectManager;

    /**
     * @var State|Mock
     */
    private $appState;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->inputValidator = $this->createMock(InputValidator::class);
        $this->consoleLoggerFactory = $this->createMock(ConsoleLoggerFactory::class);
        $this->logger = $this->createMock(ConsoleLogger::class);
        $this->objectManager = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $this->appState = $this->createMock(State::class);
        $this->deployService = $this->createMock(DeployStaticContent::class);

        $objectManagerProvider = $this->createMock(ObjectManagerProvider::class);
        $objectManagerProvider->method('get')->willReturn($this->objectManager);

        $this->command = (new ObjectManager($this))->getObject(DeployStaticContentCommand::class, [
            'inputValidator' => $this->inputValidator,
            'consoleLoggerFactory' => $this->consoleLoggerFactory,
            'options' => new DeployStaticOptions(),
            'appState' => $this->appState,
            'objectManagerProvider' => $objectManagerProvider
        ]);
    }

    /**
     * @param array $input
     * @see DeployStaticContentCommand::execute()
     * @dataProvider executeDataProvider
     */
    public function testExecute($input)
    {
        $this->appState->expects($this->once())
            ->method('getMode')
            ->willReturn(State::MODE_PRODUCTION);

        $this->inputValidator->expects($this->once())
            ->method('validate');

        $this->consoleLoggerFactory->expects($this->once())
            ->method('getLogger')->willReturn($this->logger);
        $this->logger->expects($this->exactly(2))->method('alert');

        $this->objectManager->expects($this->once())->method('create')->willReturn($this->deployService);
        $this->deployService->expects($this->once())->method('deploy');

        $tester = new CommandTester($this->command);
        $tester->execute($input);
    }

    public function executeDataProvider()
    {
        return [
            'No options' => [
                []
            ],
            'With static content version option' => [
                ['--content-version' => '123456']
            ]
        ];
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
