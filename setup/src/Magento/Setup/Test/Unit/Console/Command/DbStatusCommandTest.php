<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Console\Command;

use Magento\Framework\Module\DbVersionInfo;
use Magento\Setup\Console\Command\DbStatusCommand;
use Symfony\Component\Console\Tester\CommandTester;

class DbStatusCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Module\DbVersionInfo|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dbVersionInfo;

    /**
     * @var DbStatusCommand
     */
    private $command;

    protected function setUp()
    {
        $this->dbVersionInfo = $this->getMock('Magento\Framework\Module\DbVersionInfo', [], [], '', false);
        $objectManagerProvider = $this->getMock('Magento\Setup\Model\ObjectManagerProvider', [], [], '', false);
        $objectManager = $this->getMockForAbstractClass('Magento\Framework\ObjectManagerInterface');
        $objectManagerProvider->expects($this->any())
            ->method('get')
            ->will($this->returnValue($objectManager));
        $objectManager->expects($this->any())
            ->method('get')
            ->will($this->returnValue($this->dbVersionInfo));
        $this->command = new DbStatusCommand($objectManagerProvider);
    }

    /**
     * @param array $outdatedInfo
     * @param string $expectedMessage
     *
     * @dataProvider executeDataProvider
     */
    public function testExecute(array $outdatedInfo, $expectedMessage)
    {
        $this->dbVersionInfo->expects($this->once())
            ->method('getDbVersionErrors')
            ->will($this->returnValue($outdatedInfo));
        $tester = new CommandTester($this->command);
        $tester->execute([]);
        $this->assertStringMatchesFormat($expectedMessage, $tester->getDisplay());
    }

    public function executeDataProvider()
    {
        return [
            'DB is up to date' => [
                [],
                'All modules are up to date%a'
            ],
            'DB is outdated'   => [
                [
                    [
                        DbVersionInfo::KEY_MODULE => 'module_a',
                        DbVersionInfo::KEY_TYPE => 'schema',
                        DbVersionInfo::KEY_CURRENT => '1.0.0',
                        DbVersionInfo::KEY_REQUIRED => '2.0.0'
                    ]
                ],
                '%amodule_a%aschema%a1%a->%a2'
                . '%aRun the "Update" command to update your DB schema and data%a',
            ],
            'code is outdated' => [
                [
                    [
                        DbVersionInfo::KEY_MODULE => 'module_a',
                        DbVersionInfo::KEY_TYPE => 'data',
                        DbVersionInfo::KEY_CURRENT => '2.0.0',
                        DbVersionInfo::KEY_REQUIRED => '1.0.0'
                    ]
                ],
                '%amodule_a%adata%a2.0.0%a->%a1.0.0'
                . '%aSome modules use code versions newer or older than the database%a',
            ],
            'both DB and code is outdated' => [
                [
                    [
                        DbVersionInfo::KEY_MODULE => 'module_a',
                        DbVersionInfo::KEY_TYPE => 'schema',
                        DbVersionInfo::KEY_CURRENT => '1.0.0',
                        DbVersionInfo::KEY_REQUIRED => '2.0.0'
                    ],
                    [
                        DbVersionInfo::KEY_MODULE => 'module_b',
                        DbVersionInfo::KEY_TYPE => 'data',
                        DbVersionInfo::KEY_CURRENT => '2.0.0',
                        DbVersionInfo::KEY_REQUIRED => '1.0.0'
                    ]
                ],
                '%amodule_a%aschema%a1.0.0%a->%a2.0.0'
                . '%amodule_b%adata%a2.0.0%a->%a1.0.0'
                . '%aSome modules use code versions newer or older than the database%a',
            ],
        ];
    }
}
