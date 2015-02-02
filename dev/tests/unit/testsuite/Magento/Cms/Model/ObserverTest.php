<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Model;

/**
 * @covers \Magento\Cms\Model\Observer
 */
class ObserverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Cms\Model\Observer
     */
    protected $this;

    /**
     * @var \Magento\Cms\Helper\Page|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $cmsPageMock;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var \Magento\Framework\Event\Observer|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $observerMock;

    /**
     * @var \Magento\Framework\Event|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventMock;

    /**
     * @var \Magento\Framework\Object|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectMock;

    protected function setUp()
    {
        $this->cmsPageMock = $this
            ->getMockBuilder('Magento\Cms\Helper\Page')
            ->disableOriginalConstructor()
            ->getMock();
        $this->scopeConfigMock = $this
            ->getMockBuilder('Magento\Framework\App\Config\ScopeConfigInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->observerMock = $this
            ->getMockBuilder('Magento\Framework\Event\Observer')
            ->disableOriginalConstructor()
            ->getMock();
        $this->eventMock = $this
            ->getMockBuilder('Magento\Framework\Event')
            ->setMethods(
                [
                    'getStatus',
                    'getRedirect',
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectMock = $this
            ->getMockBuilder('Magento\Framework\Object')
            ->setMethods(
                [
                    'setLoaded',
                    'setForwardModule',
                    'setForwardController',
                    'setForwardAction',
                    'setRedirectUrl',
                    'setRedirect',
                    'setPath',
                    'setArguments',
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->this = $objectManager->getObject(
            'Magento\Cms\Model\Observer',
            [
                'cmsPage' => $this->cmsPageMock,
                'scopeConfig' => $this->scopeConfigMock
            ]
        );
    }

    /**
     * @covers \Magento\Cms\Model\Observer::noRoute
     */
    public function testNoRoute()
    {
        $this->observerMock
            ->expects($this->atLeastOnce())
            ->method('getEvent')
            ->willReturn($this->eventMock);
        $this->eventMock
            ->expects($this->atLeastOnce())
            ->method('getStatus')
            ->willReturn($this->objectMock);
        $this->objectMock
            ->expects($this->atLeastOnce())
            ->method('setLoaded')
            ->with(true)
            ->willReturnSelf();
        $this->objectMock
            ->expects($this->atLeastOnce())
            ->method('setForwardModule')
            ->with('cms')
            ->willReturnSelf();
        $this->objectMock
            ->expects($this->atLeastOnce())
            ->method('setForwardController')
            ->with('index')
            ->willReturnSelf();
        $this->objectMock
            ->expects($this->atLeastOnce())
            ->method('setForwardAction')
            ->with('noroute')
            ->willReturnSelf();

        $this->assertEquals($this->this, $this->this->noRoute($this->observerMock));
    }

    /**
     * @covers \Magento\Cms\Model\Observer::noCookies
     * @param string $pageUrl
     * @dataProvider noCookiesDataProvider
     */
    public function testNoCookies($pageUrl)
    {
        $pageId = 1;

        $this->observerMock
            ->expects($this->atLeastOnce())
            ->method('getEvent')
            ->willReturn($this->eventMock);
        $this->eventMock
            ->expects($this->atLeastOnce())
            ->method('getRedirect')
            ->willReturn($this->objectMock);
        $this->scopeConfigMock
            ->expects($this->atLeastOnce())
            ->method('getValue')
            ->with('web/default/cms_no_cookies', 'store')
            ->willReturn($pageId);
        $this->cmsPageMock
            ->expects($this->atLeastOnce())
            ->method('getPageUrl')
            ->with($pageId)
            ->willReturn($pageUrl);
        $this->objectMock
            ->expects($this->any())
            ->method('setRedirectUrl')
            ->with($pageUrl)
            ->willReturnSelf();
        $this->objectMock
            ->expects($this->any())
            ->method('setRedirect')
            ->with(true)
            ->willReturnSelf();
        $this->objectMock
            ->expects($this->any())
            ->method('setPath')
            ->with('cms/index/noCookies')
            ->willReturnSelf();
        $this->objectMock
            ->expects($this->any())
            ->method('setArguments')
            ->with([])
            ->willReturnSelf();

        $this->assertEquals($this->this, $this->this->noCookies($this->observerMock));
    }

    public function noCookiesDataProvider()
    {
        return [
            'url IS empty' => ['pageUrl' => ''],
            'url NOT empty' => ['pageUrl' => '/some/url']
        ];
    }
}
