<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Console;

use Magento\Framework\Console\GenerationDirectoryAccess;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Setup\Console\Command\DiCompileCommand;
use Magento\Setup\Mvc\Bootstrap\InitParamListener;
use Magento\Framework\Filesystem\Driver\File;
use Symfony\Component\Console\Input\ArgvInput;
use Zend\ServiceManager\ServiceManager;
use Magento\Setup\Console\CompilerPreparation;
use PHPUnit\Framework\MockObject\MockObject as Mock;

class CompilerPreparationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CompilerPreparation|Mock
     */
    private $model;

    /**
     * @var ServiceManager|Mock
     */
    private $serviceManagerMock;

    /**
     * @var ArgvInput|Mock
     */
    private $inputMock;

    /**
     * @var File|Mock
     */
    private $filesystemDriverMock;

    /**
     * @var GenerationDirectoryAccess|Mock
     */
    private $generationDirectoryAccessMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->serviceManagerMock = $this->getMockBuilder(ServiceManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->inputMock = $this->getMockBuilder(ArgvInput::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->filesystemDriverMock = $this->getMockBuilder(File::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->generationDirectoryAccessMock = $this->getMockBuilder(GenerationDirectoryAccess::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = (new ObjectManager($this))->getObject(
            CompilerPreparation::class,
            [
                'serviceManager' => $this->serviceManagerMock,
                'input' => $this->inputMock,
                'filesystemDriver' => $this->filesystemDriverMock,
                'generationDirectoryAccess' => $this->generationDirectoryAccessMock,
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
        $this->inputMock->expects($this->once())
            ->method('getFirstArgument')
            ->willReturn($commandName);
        $this->inputMock->expects($this->atLeastOnce())
            ->method('hasParameterOption')
            ->with($this->logicalOr('--help', '-h'))
            ->willReturn($isHelpOption);

        if ($isCompileCommand && !$isHelpOption) {
            $this->filesystemDriverMock->expects($this->exactly(2))
                ->method('isExists')
                ->willReturn($dirExists);
            $this->filesystemDriverMock->expects($this->exactly(((int)$dirExists) * 2))
                ->method('deleteDirectory');
        } else {
            $this->filesystemDriverMock->expects($this->never())
                ->method('isExists');
            $this->filesystemDriverMock->expects($this->never())
                ->method('deleteDirectory');
        }

        $this->generationDirectoryAccessMock->expects($this->any())
            ->method('check')
            ->willReturn(true);

        $this->model->handleCompilerEnvironment();
    }

    /**
     * @return array
     */
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
            ],
            'ST compiler, directory exists, abbreviation 1' => [
                'commandName' => 's:d:c',
                'isCompileCommand' => true,
                'isHelpOption' => false,
                'dirExists' => true
            ],
            'ST compiler, directory exists, abbreviation 2' => [
                'commandName' => 'se:di:co',
                'isCompileCommand' => true,
                'isHelpOption' => false,
                'dirExists' => true
            ],
            'ST compiler, directory exists, abbreviation ambiguous' => [
                'commandName' => 'se:di',
                'isCompileCommand' => false,
                'isHelpOption' => false,
                'dirExists' => true
            ],
        ];
    }

    public function testGenerationDirectoryFromInitParams()
    {
        $customGenerationDirectory = '/custom/generated/code/directory';
        $defaultDiDirectory = '/custom/di/directory';
        $mageInitParams = ['MAGE_DIRS' => ['generation' => ['path' => $customGenerationDirectory]]];
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

        $this->inputMock->expects($this->once())
            ->method('getFirstArgument')
            ->willReturn(DiCompileCommand::NAME);
        $this->filesystemDriverMock->expects($this->exactly(2))
            ->method('isExists')
            ->willReturn(true);
        $this->filesystemDriverMock->expects($this->exactly(2))
            ->method('deleteDirectory')
            ->willReturnMap($dirValueMap);
        $this->serviceManagerMock->expects($this->once())
            ->method('get')
            ->with(InitParamListener::BOOTSTRAP_PARAM)
            ->willReturn($mageInitParams);
        $this->generationDirectoryAccessMock->expects($this->once())
            ->method('check')
            ->willReturn(true);

        $this->model->handleCompilerEnvironment();
    }

    public function testGenerationDirectoryFromCliOption()
    {
        $customGenerationDirectory = '/custom/generated/code/directory';
        $customDiDirectory = '/custom/di/directory';
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

        $this->inputMock->expects($this->once())
            ->method('getFirstArgument')
            ->willReturn(DiCompileCommand::NAME);
        $this->filesystemDriverMock->expects($this->exactly(2))
            ->method('isExists')
            ->willReturn(true);
        $this->filesystemDriverMock->expects($this->exactly(2))
            ->method('deleteDirectory')
            ->willReturnMap($dirResultMap);
        $this->generationDirectoryAccessMock->expects($this->once())
            ->method('check')
            ->willReturn(true);

        $this->model->handleCompilerEnvironment();
    }
}
