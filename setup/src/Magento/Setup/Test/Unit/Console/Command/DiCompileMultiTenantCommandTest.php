<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Console\Command;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Setup\Console\Command\DiCompileMultiTenantCommand;
use Symfony\Component\Console\Tester\CommandTester;

class DiCompileMultiTenantCommandTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Setup\Console\Command\DiCompileMultiTenantCommand */
    private $model;

    public function setUp()
    {
        $this->model = (new ObjectManager($this))->getObject(
            '\Magento\Setup\Console\Command\DiCompileMultiTenantCommand'
        );
    }

    /**
     * @dataProvider validateDataProvider
     * @param array $option
     * @param string $error
     */
    public function testExecuteInvalidData(array $option, $error)
    {
        $objectManagerProvider = $this->getMock(
            'Magento\Setup\Model\ObjectManagerProvider',
            [],
            [],
            '',
            false
        );
        $objectManager = $this->getMockForAbstractClass(
            'Magento\Framework\ObjectManagerInterface',
            [],
            '',
            false
        );
        $objectManagerProvider->expects($this->once())->method('get')->willReturn($objectManager);
        $directoryList = $this->getMock('Magento\Framework\App\Filesystem\DirectoryList', [], [], '', false);
        $componentRegistrar = $this->getMock(
            '\Magento\Framework\Component\ComponentRegistrar',
            [],
            [],
            '',
            false
        );
        $componentRegistrar->expects($this->any())->method('getPaths')->willReturnMap([
            [ComponentRegistrar::MODULE, ['/path/to/module/one', '/path/to/module/two']],
            [ComponentRegistrar::LIBRARY, ['/path/to/library/one', '/path/to/library/two']],
        ]);
        $command = new DiCompileMultiTenantCommand($objectManagerProvider, $directoryList, $componentRegistrar);
        $commandTester = new CommandTester($command);
        $commandTester->execute($option);
        $this->assertEquals($error . PHP_EOL, $commandTester->getDisplay());
    }

    /**
     * @return array
     */
    public function validateDataProvider()
    {
        return [
            [
                ['--' . DiCompileMultiTenantCommand::INPUT_KEY_SERIALIZER => 'invalidSerializer'],
                'Invalid value for command option \'' . DiCompileMultiTenantCommand::INPUT_KEY_SERIALIZER
                . '\'. Possible values (serialize|igbinary).'
            ],
            [
                ['--' . DiCompileMultiTenantCommand::INPUT_KEY_EXTRA_CLASSES_FILE => '/wrong/file/path'],
                'Path does not exist for the value of command option \''
                . DiCompileMultiTenantCommand::INPUT_KEY_EXTRA_CLASSES_FILE . '\'.'
            ],
            [
                ['--' . DiCompileMultiTenantCommand::INPUT_KEY_GENERATION => '/wrong/path'],
                'Path does not exist for the value of command option \''
                . DiCompileMultiTenantCommand::INPUT_KEY_GENERATION . '\'.'
            ],
            [
                ['--' . DiCompileMultiTenantCommand::INPUT_KEY_DI => '/wrong/path'],
                'Path does not exist for the value of command option \''
                . DiCompileMultiTenantCommand::INPUT_KEY_DI . '\'.'
            ],
            [
                ['--' . DiCompileMultiTenantCommand::INPUT_KEY_EXCLUDE_PATTERN => '%wrongPattern'],
                'Invalid pattern for command option \''
                . DiCompileMultiTenantCommand::INPUT_KEY_EXCLUDE_PATTERN . '\'.'
            ],
        ];
    }

    public function testConfigure()
    {
        $methodUnderTest = new \ReflectionMethod($this->model, 'configure');
        $methodUnderTest->setAccessible(true);
        $methodUnderTest->invoke($this->model);
        $this->assertSame(DiCompileMultiTenantCommand::NAME, $this->model->getName());
        $this->assertNotEmpty($this->model->getDescription());
        $this->assertCount(6, $this->model->getDefinition()->getOptions());
    }
}
