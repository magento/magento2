<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Test\Unit\Model;

use Magento\Deploy\Model\Deploy\LocaleDeploy;
use Magento\Deploy\Model\DeployStrategyFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class DeployStrategyFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManagerMock;

    /**
     * @var DeployStrategyFactory
     */
    private $unit;

    protected function setUp()
    {
        $this->objectManagerMock = $this->getMock(ObjectManagerInterface::class);

        $this->unit = (new ObjectManager($this))->getObject(
            DeployStrategyFactory::class,
            [
                'objectManager' => $this->objectManagerMock,
            ]
        );
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Wrong deploy strategy type: wrong-type
     */
    public function testCreateWithWrongStrategyType()
    {
        $this->unit->create('wrong-type');
    }

    public function testCreate()
    {
        $this->objectManagerMock->expects(self::once())->method('create')
            ->with(LocaleDeploy::class, ['arg1' => 1]);

        $this->unit->create(DeployStrategyFactory::DEPLOY_STRATEGY_STANDARD, ['arg1' => 1]);
    }
}
