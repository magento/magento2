<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Cms\Test\Unit\Observer;

use Magento\Cms\Helper\Page;
use Magento\Cms\Observer\NoCookiesObserver;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class NoCookiesObserverTest extends TestCase
{
    /**
     * @var NoCookiesObserver
     */
    protected $noCookiesObserver;

    /**
     * @var Page|MockObject
     */
    protected $cmsPageMock;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var Observer|MockObject
     */
    protected $observerMock;

    /**
     * @var Event|MockObject
     */
    protected $eventMock;

    /**
     * @var DataObject|MockObject
     */
    protected $objectMock;

    protected function setUp(): void
    {
        $this->cmsPageMock = $this
            ->getMockBuilder(Page::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->scopeConfigMock = $this
            ->getMockBuilder(ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->observerMock = $this
            ->getMockBuilder(Observer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->eventMock = $this
            ->getMockBuilder(Event::class)
            ->setMethods(
                [
                    'getStatus',
                    'getRedirect',
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectMock = $this
            ->getMockBuilder(DataObject::class)
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

        $objectManager = new ObjectManager($this);
        $this->noCookiesObserver = $objectManager->getObject(
            NoCookiesObserver::class,
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
