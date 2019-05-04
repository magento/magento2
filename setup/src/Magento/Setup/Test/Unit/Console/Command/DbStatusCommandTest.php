<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Console\Command;

use Magento\Framework\Console\Cli;
use Magento\Framework\Module\DbVersionInfo;
use Magento\Framework\Setup\UpToDateValidatorInterface;
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
     * @var array | Mock[]
     */
    private $validators;

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
        $this->validators = [
            'declarative_schema' => $this->getMockBuilder(UpToDateValidatorInterface::class)->getMock(),
            'up_to_date_schema' => $this->getMockBuilder(UpToDateValidatorInterface::class)->getMock(),
            'up_to_date_data' => $this->getMockBuilder(UpToDateValidatorInterface::class)->getMock(),
            'old_validator' => $this->getMockBuilder(UpToDateValidatorInterface::class)->getMock(),
        ];

        $objectManagerProvider->expects($this->any())
            ->method('get')
            ->will($this->returnValue($objectManager));
        $objectManager->expects(self::exactly(4))
            ->method('get')
            ->willReturnOnConsecutiveCalls(
                $this->validators['declarative_schema'],
                $this->validators['up_to_date_schema'],
                $this->validators['up_to_date_data'],
                $this->validators['old_validator']
            );
        $this->command = new DbStatusCommand($objectManagerProvider, $this->deploymentConfig);
    }

    public function testExecute()
    {
        $this->validators['old_validator']->expects(self::once())
            ->method('isUpToDate')
            ->willReturn(true);
        $this->validators['up_to_date_schema']->expects(self::once())
            ->method('isUpToDate')
            ->willReturn(true);
        $this->validators['up_to_date_data']->expects(self::once())
            ->method('isUpToDate')
            ->willReturn(true);
        $this->validators['declarative_schema']->expects(self::once())
            ->method('isUpToDate')
            ->willReturn(true);
        $this->deploymentConfig->expects($this->once())
            ->method('isAvailable')
            ->will($this->returnValue(true));
        $tester = new CommandTester($this->command);
        $tester->execute([]);
        $this->assertStringMatchesFormat('All modules are up to date.', $tester->getDisplay());
        $this->assertSame(0, $tester->getStatusCode());
    }

    public function testExecuteNotInstalled()
    {
        $this->deploymentConfig->expects($this->once())
            ->method('isAvailable')
            ->will($this->returnValue(false));
        $tester = new CommandTester($this->command);
        $tester->execute([]);

        $this->assertStringMatchesFormat(
            'No information is available: the Magento application is not installed.%w',
            $tester->getDisplay()
        );
        $this->assertSame(Cli::RETURN_FAILURE, $tester->getStatusCode());
    }
}
