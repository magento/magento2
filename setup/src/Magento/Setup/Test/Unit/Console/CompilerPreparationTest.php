<?php
/***
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Console;


use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Setup\Console\Command\DiCompileCommand;
use Magento\Setup\Console\Command\DiCompileMultiTenantCommand;
use Magento\Setup\Mvc\Bootstrap\InitParamListener;
use Symfony\Component\Console\Input\ArgvInput;

class CompilerPreparationTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Setup\Console\CompilerPreparation */
    private $model;

    /** @var \Zend\ServiceManager\ServiceManager | \PHPUnit_Framework_MockObject_MockObject */
    private $serviceManagerMock;

    /** @var \Symfony\Component\Console\Input\ArgvInput | \PHPUnit_Framework_MockObject_MockObject */
    private $inputMock;

    /** @var \Magento\Framework\Filesystem\Driver\File | \PHPUnit_Framework_MockObject_MockObject */
    private $filesystemDriverMock;

    public function setUp()
    {
        $this->serviceManagerMock = $this->getMockBuilder('\Zend\ServiceManager\ServiceManager')
            ->disableOriginalConstructor()
            ->getMock();
        $this->inputMock = $this->getMockBuilder('\Symfony\Component\Console\Input\ArgvInput')
            ->disableOriginalConstructor()
            ->getMock();
        $this->filesystemDriverMock = $this->getMockBuilder('\Magento\Framework\Filesystem\Driver\File')
            ->disableOriginalConstructor()
            ->getMock();
        $this->model = (new ObjectManager($this))->getObject(
            '\Magento\Setup\Console\CompilerPreparation',
            [
                'serviceManager' => $this->serviceManagerMock,
                'input' => $this->inputMock,
                'filesystemDriver' => $this->filesystemDriverMock
            ]
        );
    }

    /**
     * @dataProvider commandNameDataProvider
     * @param $commandName
     * @param $isCompileCommand
     * @param $isHelpOption
     * @param bool|null $dirExists
     */
    public function testClearGenerationDirWhenNeeded($commandName, $isCompileCommand, $isHelpOption, $dirExists = false)
    {
        $this->inputMock->expects($this->once())->method('getFirstArgument')->willReturn($commandName);
        $this->inputMock->expects($this->atLeastOnce())
            ->method('hasParameterOption')
            ->with(
                $this->logicalOr('--help', '-h')
            )->willReturn($isHelpOption);
        if ($isCompileCommand && !$isHelpOption) {
            $this->filesystemDriverMock->expects($this->once())
                ->method('isExists')
                ->willReturn($dirExists);
            $this->filesystemDriverMock->expects($this->exactly((int)$dirExists))->method('deleteDirectory');
        } else {
            $this->filesystemDriverMock->expects($this->never())->method('isExists');
            $this->filesystemDriverMock->expects($this->never())->method('deleteDirectory');
        }
        $this->model->handleCompilerEnvironment();
    }

    public function commandNameDataProvider()
    {
        return [
            'ST compiler, directory exists' => [
                'commandName' => DiCompileCommand::NAME,
                'isCompileCommand' => true,
                'isHelpOption' => false,
                'dirExists' => true
            ],
            'ST compiler, directory does not exist' => [
                'commandName' => DiCompileCommand::NAME,
                'isCompileCommand' => true,
                'isHelpOption' => false,
                'dirExists' => false
            ],
            'ST compiler, help option' => [
                'commandName' => DiCompileCommand::NAME,
                'isCompileCommand' => true,
                'isHelpOption' => true,
                'dirExists' => false
            ],
            'MT compiler, directory exists' => [
                'commandName' => DiCompileMultiTenantCommand::NAME,
                'isCompileCommand' => true,
                'isHelpOption' => false,
                'dirExists' => true
            ],
            'MT compiler, directory does not exist' => [
                'commandName' => DiCompileMultiTenantCommand::NAME,
                'isCompileCommand' => true,
                'isHelpOption' => false,
                'dirExists' => false
            ],
            'MT compiler, help option' => [
                'commandName' => DiCompileMultiTenantCommand::NAME,
                'isCompileCommand' => true,
                'isHelpOption' => true,
                'dirExists' => true
            ],
            'Other command' => [
                'commandName' => 'not:a:compiler',
                'isCompileCommand' => false,
                'isHelpOption' => false,
            ]
        ];
    }

    public function testGenerationDirectoryFromInitParams()
    {
        $customGenerationDirectory = '/custom/generated/code/directory';
        $mageInitParams = ['MAGE_DIRS' => ['generation' => ['path' => $customGenerationDirectory]]];

        $this->inputMock->expects($this->once())
            ->method('getFirstArgument')
            ->willReturn(DiCompileMultiTenantCommand::NAME);

        // Filesystem mock
        $this->filesystemDriverMock->expects($this->once())->method('isExists')->willReturn(true);
        $this->filesystemDriverMock->expects($this->once())
            ->method('deleteDirectory')
            ->with($customGenerationDirectory);

        $this->serviceManagerMock->expects($this->once())
            ->method('get')
            ->with(InitParamListener::BOOTSTRAP_PARAM)
            ->willReturn($mageInitParams);
        $this->model->handleCompilerEnvironment();
    }

    /**
     * @dataProvider compilerCommandDataProvider
     */
    public function testGenerationDirectoryFromCliOption($commandName)
    {
        $customGenerationDirectory = '/custom/generated/code/directory';
        $useCliOption = $commandName === DiCompileMultiTenantCommand::NAME;

        $this->inputMock->expects($this->once())
            ->method('getFirstArgument')
            ->willReturn($commandName);
        $this->inputMock->expects($this->exactly((int)$useCliOption))
            ->method('getParameterOption')
            ->with(DiCompileMultiTenantCommand::INPUT_KEY_GENERATION)
            ->willReturn($customGenerationDirectory);
        // Filesystem mock
        $directoryArgConstraint = $useCliOption
            ? $this->equalTo($customGenerationDirectory)
            : $this->logicalNot($this->equalTo($customGenerationDirectory));
        $this->filesystemDriverMock->expects($this->once())->method('isExists')->willReturn(true);
        $this->filesystemDriverMock->expects($this->once())
            ->method('deleteDirectory')
            ->with($directoryArgConstraint);

        $this->model->handleCompilerEnvironment();
    }

    public function compilerCommandDataProvider()
    {
        return [
            [DiCompileCommand::NAME],
            [DiCompileMultiTenantCommand::NAME]
        ];
    }
}
