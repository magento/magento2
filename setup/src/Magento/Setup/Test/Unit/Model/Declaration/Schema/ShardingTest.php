<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Model\Declaration\Schema;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class ShardingTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Setup\Model\Declaration\Schema\Sharding */
    protected $model;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\Framework\App\DeploymentConfig|\PHPUnit_Framework_MockObject_MockObject */
    protected $deploymentConfigMock;

    /** @var \SafeReflectionClass|\PHPUnit_Framework_MockObject_MockObject */
    protected $safeReflectionClassMock;

    protected function setUp()
    {
        $this->deploymentConfigMock = $this->getMockBuilder(\Magento\Framework\App\DeploymentConfig::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $this->objectManagerHelper->getObject(
            \Magento\Setup\Model\Declaration\Schema\Sharding::class,
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
