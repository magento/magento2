<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Setup\Test\Unit\Declaration\Schema;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class ShardingTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Framework\Setup\Declaration\Schema\Sharding */
    private $model;

    /** @var ObjectManagerHelper */
    private $objectManagerHelper;

    /** @var DeploymentConfig|\PHPUnit_Framework_MockObject_MockObject */
    private $deploymentConfigMock;

    protected function setUp()
    {
        $this->deploymentConfigMock = $this->getMockBuilder(DeploymentConfig::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $this->objectManagerHelper->getObject(
            \Magento\Framework\Setup\Declaration\Schema\Sharding::class,
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
