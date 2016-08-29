<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Test\Unit\Model;

use Magento\Deploy\Model\Deploy\LocaleDeploy;
use Magento\Deploy\Model\DeployStrategyFactory;
use Magento\Framework\App\ObjectManagerFactory;
use Magento\Framework\App\State;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class DeployStrategyFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManagerFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManagerFactoryMock;

    /**
     * @var DeployStrategyFactory
     */
    private $unit;

    protected function setUp()
    {
        $this->objectManagerFactoryMock = $this->getMock(
            ObjectManagerFactory::class,
            [],
            [],
            '',
            false
        );

        $this->unit = (new ObjectManager($this))->getObject(
            DeployStrategyFactory::class,
            [
                'objectManagerFactory' => $this->objectManagerFactoryMock,
            ]
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Wrong deploy strategy type: wrong-type
     */
    public function testCreateWithWrongStrategyType()
    {
        $this->unit->create('adminhtml', 'wrong-type');
    }

    public function testCreate()
    {
        $areaCode = 'adminhtml';

        $objectManagerMock = $this->getMockBuilder(\Magento\Framework\App\ObjectManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $stateMock = $this->getMockBuilder(State::class)
            ->disableOriginalConstructor()
            ->getMock();
        $stateMock->expects(self::once())->method('setAreaCode')->with($areaCode)->willReturnSelf();

        $this->objectManagerFactoryMock->expects(self::once())->method('create')
            ->with([State::PARAM_MODE => State::MODE_PRODUCTION])
            ->willReturn($objectManagerMock);

        $objectManagerMock->expects(self::once())->method('get')->willReturn($stateMock);
        $objectManagerMock->expects(self::once())->method('create')
            ->with(LocaleDeploy::class, ['arg1' => 1]);

        $this->unit->create($areaCode, DeployStrategyFactory::DEPLOY_STRATEGY_STANDARD, ['arg1' => 1]);
    }
}
