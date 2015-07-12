<?php
/***
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Developer\Test\Unit\Model\View\Asset\PreProcessor;

use Magento\Developer\Model\Config\Source\WorkflowType;
use Magento\Developer\Model\View\Asset\PreProcessor\DeveloperChainFactory;

class DeveloperChainFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @var  \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\ObjectManagerInterface */
    private $objectManagerMock;

    /** @var  \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\App\Config\ScopeConfigInterface */
    private $configMock;

    /** @var  \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\View\Asset\PreProcessor\ChainFactory */
    private $chainFactoryMock;

    /** @var  \PHPUnit_Framework_MockObject_MockObject | DeveloperChainFactory */
    private $model;

    public function setUp()
    {
        // Set up mocks
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->objectManagerMock = $this->getMockBuilder('Magento\Framework\ObjectManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->configMock = $this->getMockBuilder('Magento\Framework\App\Config\ScopeConfigInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->chainFactoryMock = $this->getMockBuilder('Magento\Framework\View\Asset\PreProcessor\ChainFactory')
            ->disableOriginalConstructor()
            ->getMock();

        // Set up System Under Test
        $sutArgs = [
            'objectManager' => $this->objectManagerMock,
            'chainFactory' => $this->chainFactoryMock,
            'scopeConfig' => $this->configMock
        ];
        $this->model = $objectManager->getObject(
            'Magento\Developer\Model\View\Asset\PreProcessor\DeveloperChainFactory',
            $sutArgs
        );
    }

    public function testCreateClientCompilation()
    {
        $this->configMock->expects($this->once())
            ->method('getValue')
            ->with(WorkflowType::CONFIG_NAME_PATH)
            ->willReturn(WorkflowType::CLIENT_SIDE_COMPILATION);
        $instanceMock = $this->getMockBuilder(DeveloperChainFactory::ENTITY_NAME)
            ->disableOriginalConstructor()
            ->getMock();
        $createArgs = [1, 2, 3];
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with(DeveloperChainFactory::ENTITY_NAME, $createArgs)
            ->willReturn($instanceMock);
        $this->chainFactoryMock->expects($this->never())->method('create');

        $this->assertSame($instanceMock, $this->model->create($createArgs));
    }

    public function testCreateNoClientCompilation()
    {
        $this->configMock->expects($this->once())
            ->method('getValue')
            ->with(WorkflowType::CONFIG_NAME_PATH)
            ->willReturn('');
        $instanceMock = $this->getMockBuilder(DeveloperChainFactory::ENTITY_NAME)
            ->disableOriginalConstructor()
            ->getMock();
        $createArgs = [1, 2, 3];
        $this->chainFactoryMock->expects($this->once())
            ->method('create')
            ->with($createArgs)
            ->willReturn($instanceMock);
        $this->objectManagerMock->expects($this->never())->method('create');

        $this->assertSame($instanceMock, $this->model->create($createArgs));
    }
}
