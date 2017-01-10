<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Test\Unit\Render;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class RenderFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\View\Render\RenderFactory */
    protected $renderFactory;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $objectManagerMock;

    protected function setUp()
    {
        $this->objectManagerMock = $this->getMock(\Magento\Framework\ObjectManagerInterface::class);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->renderFactory = $this->objectManagerHelper->getObject(
            \Magento\Framework\View\Render\RenderFactory::class,
            [
                'objectManager' => $this->objectManagerMock
            ]
        );
    }

    public function testGet()
    {
        $instance = \Magento\Framework\View\RenderInterface::class;
        $renderMock = $this->getMock($instance, [], [], '', false);
        $data = 'RenderInterface';
        $this->objectManagerMock->expects($this->once())
            ->method('get')
            ->with($this->equalTo(\Magento\Framework\View\Render\RenderInterface::class))
            ->will($this->returnValue($renderMock));
        $this->assertInstanceOf($instance, $this->renderFactory->get($data));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Type "RenderInterface" is not instance on Magento\Framework\View\RenderInterface
     */
    public function testGetException()
    {
        $this->objectManagerMock->expects($this->once())
            ->method('get')
            ->with($this->equalTo(\Magento\Framework\View\Render\RenderInterface::class))
            ->will($this->returnValue(new \stdClass()));
        $this->renderFactory->get('RenderInterface');
    }
}
