<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Test\Unit\Console\Command\App;

use Magento\Deploy\Console\Command\App\SensitiveConfigSet\CollectorFactory;
use Magento\Deploy\Console\Command\App\SensitiveConfigSet\CollectorInterface;
use Magento\Deploy\Console\Command\App\SensitiveConfigSetCommand;
use Magento\Deploy\Model\ConfigWriter;
use Magento\Framework\App\Config\CommentParserInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Scope\ValidatorInterface;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\Console\Cli;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SensitiveConfigSetCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConfigFilePool|MockObject
     */
    private $configFilePoolMock;

    /**
     * @var CommentParserInterface|MockObject
     */
    private $commentParserMock;

    /**
     * @var ConfigWriter|MockObject
     */
    private $configWriterMock;

    /**
     * @var ValidatorInterface|MockObject
     */
    private $scopeValidatorMock;

    /**
     * @var CollectorFactory|MockObject
     */
    private $collectorFactoryMock;

    /**
     * @var SensitiveConfigSetCommand
     */
    private $command;

    public function setUp()
    {
        $this->configFilePoolMock = $this->getMockBuilder(ConfigFilePool::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->commentParserMock = $this->getMockBuilder(CommentParserInterface::class)
            ->getMockForAbstractClass();
        $this->configWriterMock = $this->getMockBuilder(ConfigWriter::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->scopeValidatorMock = $this->getMockBuilder(ValidatorInterface::class)
            ->getMockForAbstractClass();
        $this->collectorFactoryMock = $this->getMockBuilder(CollectorFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->command = new SensitiveConfigSetCommand(
            $this->configFilePoolMock,
            $this->commentParserMock,
            $this->configWriterMock,
            $this->scopeValidatorMock,
            $this->collectorFactoryMock
        );
    }

    public function testConfigFileNotExist()
    {
        $this->configFilePoolMock->expects($this->once())
            ->method('getPathsByPool')
            ->with(ConfigFilePool::LOCAL)
            ->willReturn([
                ConfigFilePool::APP_CONFIG => 'config.local.php'
            ]);
        $this->scopeValidatorMock->expects($this->once())
            ->method('isValid')
            ->with('default', '')
            ->willReturn(true);
        $this->commentParserMock->expects($this->any())
            ->method('execute')
            ->willThrowException(new FileSystemException(new Phrase('some message')));

        $tester = new CommandTester($this->command);
        $tester->execute([
            'path' => 'some/path',
            'value' => 'some value'
        ]);

        $this->assertEquals(
            Cli::RETURN_FAILURE,
            $tester->getStatusCode()
        );
        $this->assertContains(
            'File app/etc/config.local.php can\'t be read. '
            . 'Please check if it exists and has read permissions.',
            $tester->getDisplay()
        );
    }

    public function testWriterException()
    {
        $exceptionMessage = 'exception';
        $this->scopeValidatorMock->expects($this->once())
            ->method('isValid')
            ->with('default', '')
            ->willReturn(true);
        $this->commentParserMock->expects($this->once())
            ->method('execute')
            ->willReturn([
                'some/config/path1',
                'some/config/path2'
            ]);
        $collectorMock = $this->getMockBuilder(CollectorInterface::class)
            ->getMockForAbstractClass();
        $collectorMock->expects($this->once())
            ->method('getValues')
            ->willReturn(['some/config/pathNotExist' => 'value']);
        $this->collectorFactoryMock->expects($this->once())
            ->method('create')
            ->with(CollectorFactory::TYPE_SIMPLE)
            ->willReturn($collectorMock);
        $this->configWriterMock->expects($this->once())
            ->method('save')
            ->willThrowException(new LocalizedException(__($exceptionMessage)));

        $tester = new CommandTester($this->command);
        $tester->execute([
            'path' => 'some/config/pathNotExist',
            'value' => 'some value'
        ]);

        $this->assertEquals(
            Cli::RETURN_FAILURE,
            $tester->getStatusCode()
        );
        $this->assertContains(
            $exceptionMessage,
            $tester->getDisplay()
        );
    }

    public function testEmptyConfigPaths()
    {
        $this->scopeValidatorMock->expects($this->once())
            ->method('isValid')
            ->with('default', '')
            ->willReturn(true);
        $this->commentParserMock->expects($this->once())
            ->method('execute')
            ->willReturn([]);

        $tester = new CommandTester($this->command);
        $tester->execute([
            'path' => 'some/config/pathNotExist',
            'value' => 'some value'
        ]);

        $this->assertEquals(
            Cli::RETURN_FAILURE,
            $tester->getStatusCode()
        );
        $this->assertContains(
            'There are no sensitive configurations to fill',
            $tester->getDisplay()
        );
    }

    public function testExecute()
    {
        $collectedValues = ['some/config/path1' => 'value'];
        $this->scopeValidatorMock->expects($this->once())
            ->method('isValid')
            ->with('default', '')
            ->willReturn(true);
        $this->commentParserMock->expects($this->once())
            ->method('execute')
            ->willReturn([
                'some/config/path1',
                'some/config/path2'
            ]);
        $collectorMock = $this->getMockBuilder(CollectorInterface::class)
            ->getMockForAbstractClass();
        $collectorMock->expects($this->once())
            ->method('getValues')
            ->willReturn($collectedValues);
        $this->collectorFactoryMock->expects($this->once())
            ->method('create')
            ->with(CollectorFactory::TYPE_SIMPLE)
            ->willReturn($collectorMock);
        $this->configWriterMock->expects($this->once())
            ->method('save')
            ->with($collectedValues, ScopeConfigInterface::SCOPE_TYPE_DEFAULT, '');

        $tester = new CommandTester($this->command);
        $tester->execute([]);

        $this->assertEquals(
            Cli::RETURN_SUCCESS,
            $tester->getStatusCode()
        );
        $this->assertContains(
            'Configuration value saved in',
            $tester->getDisplay()
        );
    }

    public function testExecuteInteractive()
    {
        $collectedValues = ['some/config/path1' => 'value'];
        $this->scopeValidatorMock->expects($this->once())
            ->method('isValid')
            ->with('default', '')
            ->willReturn(true);
        $this->commentParserMock->expects($this->once())
            ->method('execute')
            ->willReturn([
                'some/config/path1',
                'some/config/path2',
                'some/config/path3'
            ]);
        $collectorMock = $this->getMockBuilder(CollectorInterface::class)
            ->getMockForAbstractClass();
        $collectorMock->expects($this->once())
            ->method('getValues')
            ->willReturn($collectedValues);
        $this->collectorFactoryMock->expects($this->once())
            ->method('create')
            ->with(CollectorFactory::TYPE_INTERACTIVE)
            ->willReturn($collectorMock);
        $this->configWriterMock->expects($this->once())
            ->method('save')
            ->with($collectedValues, ScopeConfigInterface::SCOPE_TYPE_DEFAULT, '');

        $tester = new CommandTester($this->command);
        $tester->execute(['--interactive' => true]);

        $this->assertEquals(
            Cli::RETURN_SUCCESS,
            $tester->getStatusCode()
        );
        $this->assertContains(
            'Configuration values saved in',
            $tester->getDisplay()
        );
    }
}
