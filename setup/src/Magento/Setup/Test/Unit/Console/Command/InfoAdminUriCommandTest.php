<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Console\Command;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Setup\BackendFrontnameGenerator;
use Magento\Setup\Console\Command\InfoAdminUriCommand;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class InfoAdminUriCommandTest extends TestCase
{
    /**
     * @var DeploymentConfig|MockObject
     */
    protected $deploymentConfig;

    protected function setup(): void
    {
        $this->deploymentConfig = $this->createMock(DeploymentConfig::class);
    }

    public function testExecute()
    {
        $this->deploymentConfig->expects($this->once())->method('get')->willReturn('admin_qw12er');

        $commandTester = new CommandTester(new InfoAdminUriCommand($this->deploymentConfig));
        $commandTester->execute([]);

        $regexp = '/' . BackendFrontnameGenerator::ADMIN_AREA_PATH_PREFIX
            . '[a-z0-9]{1,' . BackendFrontnameGenerator::ADMIN_AREA_PATH_RANDOM_PART_LENGTH . '}/';

        $this->assertMatchesRegularExpression(
            $regexp,
            $commandTester->getDisplay(),
            'Unexpected Backend Frontname pattern.'
        );
    }
}
