<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class AdapterFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Search\Model\AdapterFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $adapterFactory;

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

        $this->adapterFactory = $this->helper->getObject(
            \Magento\Search\Model\AdapterFactory::class,
            [
                'objectManager' => $this->objectManager,
                'scopeConfig' => $this->scopeConfig,
                'path' => 'some_path',
                'scopeType' => 'some_scopeType',
                'adapters' => ['ClassName' => 'ClassName']
            ]
        );
    }

    public function testCreate()
    {
        $this->scopeConfig->expects($this->once())->method('getValue')
            ->with($this->equalTo('some_path'), $this->equalTo('some_scopeType'))
            ->will($this->returnValue('ClassName'));

        $adapter = $this->getMockBuilder(\Magento\Framework\Search\AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManager->expects($this->once())->method('create')
            ->with($this->equalTo('ClassName'), $this->equalTo(['input']))
            ->will($this->returnValue($adapter));

        $result = $this->adapterFactory->create(['input']);
        $this->assertInstanceOf(\Magento\Framework\Search\AdapterInterface::class, $result);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCreateExceptionThrown()
    {
        $this->scopeConfig->expects($this->once())->method('getValue')
            ->with($this->equalTo('some_path'), $this->equalTo('some_scopeType'))
            ->will($this->returnValue('ClassName'));

        $this->objectManager->expects($this->once())->method('create')
            ->with($this->equalTo('ClassName'), $this->equalTo(['input']))
            ->will($this->returnValue('t'));

        $this->adapterFactory->create(['input']);
    }

    /**
     * @expectedException \LogicException
     */
    public function testCreateLogicException()
    {
        $this->scopeConfig->expects($this->once())->method('getValue')
            ->with($this->equalTo('some_path'), $this->equalTo('some_scopeType'))
            ->will($this->returnValue('Class'));

        $this->adapterFactory->create(['input']);
    }
}
