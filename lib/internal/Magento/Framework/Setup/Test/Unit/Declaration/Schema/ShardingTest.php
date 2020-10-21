<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Setup\Test\Unit\Declaration\Schema;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Setup\Declaration\Schema\Sharding;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ShardingTest extends TestCase
{
    /** @var Sharding */
    private $model;

    /** @var ObjectManagerHelper */
    private $objectManagerHelper;

    /** @var DeploymentConfig|MockObject */
    private $deploymentConfigMock;

    protected function setUp(): void
    {
        $this->deploymentConfigMock = $this->getMockBuilder(DeploymentConfig::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $this->objectManagerHelper->getObject(
            Sharding::class,
            [
                'deploymentConfig' => $this->deploymentConfigMock,
                'resources' => ['default', 'checkout', 'sales']
            ]
        );
    }

    public function testCanUseResource()
    {
        $this->deploymentConfigMock->expects(self::once())
            ->method('get')
            ->with('db/connection')
            ->willReturn(['default']);
        self::assertFalse($this->model->canUseResource('checkout'));
    }

    public function testGetResources()
    {
        $this->deploymentConfigMock->expects(self::exactly(3))
            ->method('get')
            ->with('db/connection')
            ->willReturn(['default' => 1, 'sales' => 2, 'index' => 3]);
        self::assertEquals(['default', 'sales'], $this->model->getResources());
    }
}
