<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\PageCache\Test\Unit\Model\Cache;

class TypeTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\PageCache\Model\Cache\Type */
    protected $model;

    /** @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\Event\ManagerInterface */
    protected $eventManagerMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\App\Cache\Type\FrontendPool */
    protected $cacheFrontendPoolMock;

    public function setUp()
    {
        $this->eventManagerMock = $this->getMockBuilder('Magento\Framework\Event\ManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->cacheFrontendPoolMock = $this->getMockBuilder('Magento\Framework\App\Cache\Type\FrontendPool')
            ->disableOriginalConstructor()
            ->getMock();
        $cacheFrontend = $this->getMockBuilder('Magento\Framework\Cache\FrontendInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->cacheFrontendPoolMock->expects($this->once())
            ->method('get')
            ->willReturn($cacheFrontend);
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(
            'Magento\PageCache\Model\Cache\Type',
            [
                'eventManager' => $this->eventManagerMock,
                'cacheFrontendPool' => $this->cacheFrontendPoolMock,
            ]
        );
    }

    public function testClean()
    {
        $this->eventManagerMock->expects($this->once())
            ->method('dispatch')
            ->with('adminhtml_cache_refresh_type');

        $this->model->clean();
    }
}
