<?php
/***
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Console;


use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Setup\Console\Command\DiCompileCommand;
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
            $this->filesystemDriverMock->expects($this->exactly(2))
                ->method('isExists')
                ->willReturn($dirExists);
            $this->filesystemDriverMock->expects($this->exactly(((int)$dirExists) * 2))->method('deleteDirectory');
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
        $defaultDiDirectory = '/custom/di/directory';
        $mageInitParams = ['MAGE_DIRS' => ['generation' => ['path' => $customGenerationDirectory]]];

        $this->inputMock->expects($this->once())
            ->method('getFirstArgument')
            ->willReturn(DiCompileCommand::NAME);
        $dirValueMap = [
            [
                $customGenerationDirectory,
                $defaultDiDirectory
            ],
            [
                true,
                true
            ]
        ];
        // Filesystem mock
        $this->filesystemDriverMock->expects($this->exactly(2))->method('isExists')->willReturn(true);
        $this->filesystemDriverMock->expects($this->exactly(2))
            ->method('deleteDirectory')
            ->will($this->returnValueMap($dirValueMap));

        $this->serviceManagerMock->expects($this->once())
            ->method('get')
            ->with(InitParamListener::BOOTSTRAP_PARAM)
            ->willReturn($mageInitParams);
        $this->model->handleCompilerEnvironment();
    }

    public function testGenerationDirectoryFromCliOption()
    {
        $customGenerationDirectory = '/custom/generated/code/directory';
        $customDiDirectory = '/custom/di/directory';
        
        $this->inputMock->expects($this->once())
            ->method('getFirstArgument')
            ->willReturn(DiCompileCommand::NAME);
        $dirResultMap = [
            [
                $this->logicalNot($this->equalTo($customGenerationDirectory)),
                $this->logicalNot($this->equalTo($customDiDirectory))
            ],
            [
                true,
                true
            ]
        ];

        $this->filesystemDriverMock->expects($this->exactly(2))->method('isExists')->willReturn(true);
        $this->filesystemDriverMock->expects($this->exactly(2))
            ->method('deleteDirectory')
            ->will($this->returnValueMap($dirResultMap));

        $this->model->handleCompilerEnvironment();
    }
}
