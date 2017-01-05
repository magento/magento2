<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AdvancedSearch\Test\Unit\Model\Client;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class ClientResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\AdvancedSearch\Model\Client\ClientResolver|\PHPUnit_Framework_MockObject_MockObject
     */
    private $model;

    /**
     * @var \Magento\Framework\ObjectManagerInterface |\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManager;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $helper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeConfig;

    protected function setUp()
    {
        $this->helper = new ObjectManager($this);

        $this->scopeConfig = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManager = $this->getMock(\Magento\Framework\ObjectManagerInterface::class);

        $this->model= $this->helper->getObject(
            \Magento\AdvancedSearch\Model\Client\ClientResolver::class,
            [
                'objectManager' => $this->objectManager,
                'scopeConfig' => $this->scopeConfig,
                'clientFactories' => ['engineName' => 'engineFactoryClass'],
                'clientOptions' => ['engineName' => 'engineOptionClass'],
                'path' => 'some_path',
                'scopeType' => 'some_scopeType'
            ]
        );
    }

    public function testCreate()
    {
        $this->scopeConfig->expects($this->once())->method('getValue')
            ->with($this->equalTo('some_path'), $this->equalTo('some_scopeType'))
            ->will($this->returnValue('engineName'));

        $factoryMock = $this->getMock(\Magento\AdvancedSearch\Model\Client\ClientFactoryInterface::class);

        $clientMock = $this->getMock(\Magento\AdvancedSearch\Model\Client\ClientInterface::class);

        $clientOptionsMock = $this->getMock(\Magento\AdvancedSearch\Model\Client\ClientOptionsInterface::class);

        $this->objectManager->expects($this->exactly(2))->method('create')
            ->withConsecutive(
                [$this->equalTo('engineFactoryClass')],
                [$this->equalTo('engineOptionClass')]
            )
            ->willReturnOnConsecutiveCalls(
                $factoryMock,
                $clientOptionsMock
            );

        $clientOptionsMock->expects($this->once())->method('prepareClientOptions')
            ->with([])
            ->will($this->returnValue(['parameters']));

        $factoryMock->expects($this->once())->method('create')
            ->with($this->equalTo(['parameters']))
            ->will($this->returnValue($clientMock));

        $result = $this->model->create();
        $this->assertInstanceOf(\Magento\AdvancedSearch\Model\Client\ClientInterface::class, $result);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCreateExceptionThrown()
    {
        $this->objectManager->expects($this->once())->method('create')
            ->with($this->equalTo('engineFactoryClass'))
            ->will($this->returnValue('t'));

        $this->model->create('engineName');
    }

    /**
     * @expectedException LogicException
     */
    public function testCreateLogicException()
    {
        $this->model->create('input');
    }

    public function testGetCurrentEngine()
    {
        $this->scopeConfig->expects($this->once())->method('getValue')
            ->with($this->equalTo('some_path'), $this->equalTo('some_scopeType'))
            ->will($this->returnValue('engineName'));

        $this->assertEquals('engineName', $this->model->getCurrentEngine());
    }
}
