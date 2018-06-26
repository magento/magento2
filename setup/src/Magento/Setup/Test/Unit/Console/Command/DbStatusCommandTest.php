<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
     * @var \Magento\Framework\App\DeploymentConfig|\PHPUnit_Framework_MockObject_MockObject
     */
    private $deploymentConfig;

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
        $this->deploymentConfig = $this->getMock('Magento\Framework\App\DeploymentConfig', [], [], '', false);
        $this->command = new DbStatusCommand($objectManagerProvider, $this->deploymentConfig);
    }

    /**
     * @param array $outdatedInfo
     * @param string $expectedMessage
     *
     * @dataProvider executeDataProvider
     */
    public function testExecute(array $outdatedInfo, $expectedMessage)
    {
        $this->deploymentConfig->expects($this->once())
            ->method('isAvailable')
            ->will($this->returnValue(true));
        $this->dbVersionInfo->expects($this->once())
            ->method('getDbVersionErrors')
            ->will($this->returnValue($outdatedInfo));
        $tester = new CommandTester($this->command);
        $tester->execute([]);
        $this->assertStringMatchesFormat($expectedMessage, $tester->getDisplay());
    }

    /**
     * @return array
     */
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
                . "%aRun 'setup:upgrade' to update your DB schema and data%a",
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

    public function testExecuteNotInstalled()
    {
        $this->deploymentConfig->expects($this->once())
            ->method('isAvailable')
            ->will($this->returnValue(false));
        $this->dbVersionInfo->expects($this->never())
            ->method('getDbVersionErrors');
        $tester = new CommandTester($this->command);
        $tester->execute([]);
        $this->assertStringMatchesFormat(
            'No information is available: the Magento application is not installed.%w',
            $tester->getDisplay()
        );
    }
}
