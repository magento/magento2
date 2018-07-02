<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Test\Unit\Observer;

class NoCookiesObserverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Cms\Observer\NoCookiesObserver
     */
    protected $noCookiesObserver;

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
     * @var \Magento\Framework\DataObject|\PHPUnit_Framework_MockObject_MockObject
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
            ->getMockBuilder('Magento\Framework\DataObject')
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

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->noCookiesObserver = $objectManager->getObject(
            'Magento\Cms\Observer\NoCookiesObserver',
            [
                'cmsPage' => $this->cmsPageMock,
                'scopeConfig' => $this->scopeConfigMock
            ]
        );
    }

    /**
     * @covers \Magento\Cms\Observer\NoCookiesObserver::execute
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
            ->with('cookie/index/noCookies')
            ->willReturnSelf();
        $this->objectMock
            ->expects($this->any())
            ->method('setArguments')
            ->with([])
            ->willReturnSelf();

        $this->assertEquals($this->noCookiesObserver, $this->noCookiesObserver->execute($this->observerMock));
    }

    /**
     * @return array
     */
    public function noCookiesDataProvider()
    {
        return [
            'url IS empty' => ['pageUrl' => ''],
            'url NOT empty' => ['pageUrl' => '/some/url']
        ];
    }
}
