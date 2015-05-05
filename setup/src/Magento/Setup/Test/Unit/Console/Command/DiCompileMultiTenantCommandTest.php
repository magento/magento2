<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Console\Command;

use Magento\Setup\Console\Command\DiCompileMultiTenantCommand;

class DiCompileMultiTenantCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider validateDataProvider
     * @param array $options
     * @param string[] $errors
     */
    public function testValidate(array $options, array $errors)
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
        $command = new DiCompileMultiTenantCommand($objectManagerProvider, $directoryList);
        $inputMock = $this->getMockForAbstractClass('Symfony\Component\Console\Input\InputInterface', [], '', false);
        $inputMock->expects($this->once())->method('getOptions')->willReturn($options);
        $this->assertEquals($errors, $command->validate($inputMock));
    }

    /**
     * @return array
     */
    public function validateDataProvider()
    {
        return [
            [
                [DiCompileMultiTenantCommand::INPUT_KEY_SERIALIZER => 'invalidSerializer'],
                [
                    '<error>Invalid value for command option \'' . DiCompileMultiTenantCommand::INPUT_KEY_SERIALIZER
                    . '\'. Possible values (serialize|igbinary).</error>'
                ]
            ],
            [
                [DiCompileMultiTenantCommand::INPUT_KEY_EXTRA_CLASSES_FILE => '/wrong/file/path'],
                [
                    '<error>Path does not exist for the value of command option \''
                    . DiCompileMultiTenantCommand::INPUT_KEY_EXTRA_CLASSES_FILE . '\'.</error>'
                ]
            ],
            [
                [DiCompileMultiTenantCommand::INPUT_KEY_GENERATION => '/wrong/path'],
                [
                    '<error>Path does not exist for the value of command option \''
                    . DiCompileMultiTenantCommand::INPUT_KEY_GENERATION . '\'.</error>'
                ]
            ],
            [
                [DiCompileMultiTenantCommand::INPUT_KEY_DI => '/wrong/path'],
                [
                    '<error>Path does not exist for the value of command option \''
                    . DiCompileMultiTenantCommand::INPUT_KEY_DI . '\'.</error>'
                ]
            ],
            [
                [DiCompileMultiTenantCommand::INPUT_KEY_EXCLUDE_PATTERN => '%wrongPattern'],
                [
                    '<error>Invalid pattern for command option \''
                    . DiCompileMultiTenantCommand::INPUT_KEY_EXCLUDE_PATTERN . '\'.</error>'
                ]
            ],
        ];
    }
}
