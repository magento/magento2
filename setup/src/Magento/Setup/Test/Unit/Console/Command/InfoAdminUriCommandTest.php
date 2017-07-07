<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Console\Command;

use Magento\Setup\Console\Command\InfoAdminUriCommand;
use Symfony\Component\Console\Tester\CommandTester;
use Magento\Framework\Setup\BackendFrontnameGenerator;

class InfoAdminUriCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\DeploymentConfig|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $deploymentConfig;

    protected function setup()
    {
        $this->deploymentConfig = $this->getMock(\Magento\Framework\App\DeploymentConfig::class, [], [], '', false);
    }

    public function testExecute()
    {
        $this->deploymentConfig->expects($this->once())->method('get')->willReturn('admin_qw12er');

        $commandTester = new CommandTester(new InfoAdminUriCommand($this->deploymentConfig));
        $commandTester->execute([]);

        $regexp = '/' . BackendFrontnameGenerator::ADMIN_AREA_PATH_PREFIX
            . '[a-z0-9]{1,' . BackendFrontnameGenerator::ADMIN_AREA_PATH_RANDOM_PART_LENGTH .'}/';

        $this->assertRegExp($regexp, $commandTester->getDisplay(), 'Unexpected Backend Frontname pattern.');
    }
}
