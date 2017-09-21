<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Console\Command;

use Magento\Framework\Console\Cli;
use Magento\Framework\Module\DbVersionInfo;
use Magento\Setup\Console\Command\DbStatusCommand;
use Magento\Framework\App\DeploymentConfig;
use Magento\Setup\Model\ObjectManagerProvider;
use Magento\Framework\ObjectManagerInterface;
use PHPUnit_Framework_MockObject_MockObject as Mock;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @inheritdoc
 */
class DbStatusCommandTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var DbVersionInfo|Mock
     */
    private $dbVersionInfo;

    /**
     * @var DeploymentConfig|Mock
     */
    private $deploymentConfig;

    /**
     * @var DbStatusCommand
     */
    private $command;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->dbVersionInfo = $this->getMockBuilder(DbVersionInfo::class)
            ->disableOriginalConstructor()
            ->getMock();
        /** @var ObjectManagerProvider|Mock $objectManagerProvider */
        $objectManagerProvider = $this->getMockBuilder(ObjectManagerProvider::class)
            ->disableOriginalConstructor()
            ->getMock();
        /** @var ObjectManagerInterface|Mock $objectManager */
        $objectManager = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $this->deploymentConfig = $this->getMockBuilder(DeploymentConfig::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerProvider->expects($this->any())
            ->method('get')
            ->will($this->returnValue($objectManager));
        $objectManager->expects($this->any())
            ->method('get')
            ->will($this->returnValue($this->dbVersionInfo));

        $this->command = new DbStatusCommand($objectManagerProvider, $this->deploymentConfig);
    }

    /**
     * @param array $outdatedInfo
     * @param string $expectedMessage
     * @param int $expectedCode
     *
     * @dataProvider executeDataProvider
     */
    public function testExecute(array $outdatedInfo, $expectedMessage, $expectedCode)
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
        $this->assertSame($expectedCode, $tester->getStatusCode());
    }

    public function executeDataProvider()
    {
        return [
            'DB is up to date' => [
                [],
                'All modules are up to date%a',
                Cli::RETURN_SUCCESS
            ],
            'DB is outdated' => [
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
                DbStatusCommand::EXIT_CODE_UPGRADE_REQUIRED,
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
                Cli::RETURN_FAILURE,
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
                Cli::RETURN_FAILURE,
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
        $this->assertSame(Cli::RETURN_FAILURE, $tester->getStatusCode());
    }
}
