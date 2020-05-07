<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogSearch\Test\Unit\Controller\Advanced;

use Magento\CatalogSearch\Controller\Advanced\Result;
use Magento\CatalogSearch\Model\Advanced;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Console\Request;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\App\View;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\Manager;
use Magento\Framework\Phrase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Url;
use Magento\Framework\UrlFactory;
use Magento\Framework\View\Model\Layout\Merge;
use Magento\Framework\View\Result\Layout;
use Magento\Framework\View\Result\Page;
use Magento\Framework\Webapi\Response;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ResultTest extends TestCase
{
    /**
     * Test result action filters set before load layout scenario
     *
     * @return void
     */
    public function testResultActionFiltersSetBeforeLoadLayout()
    {
        $filters = null;
        $expectedQuery = 'filtersData';

        $view = $this->createPartialMock(
            View::class,
            ['loadLayout', 'renderLayout', 'getPage', 'getLayout']
        );
        $update = $this->createPartialMock(Merge::class, ['getHandles']);
        $update->expects($this->once())->method('getHandles')->willReturn([]);
        $layout = $this->getMockBuilder(Layout::class)
            ->addMethods(['getUpdate'])
            ->disableOriginalConstructor()
            ->getMock();
        $layout->expects($this->once())->method('getUpdate')->willReturn($update);
        $view->expects($this->once())->method('getLayout')->willReturn($layout);
        $page = $this->createPartialMock(Page::class, ['initLayout']);
        $view->expects($this->once())->method('getPage')->willReturn($page);
        $view->expects($this->once())->method('loadLayout')->willReturnCallback(
            function () use (&$filters, $expectedQuery) {
                $this->assertEquals($expectedQuery, $filters);
            }
        );

        $request = $this->getMockBuilder(Request::class)
            ->addMethods(['getQueryValue'])
            ->disableOriginalConstructor()
            ->getMock();
        $request->expects($this->once())->method('getQueryValue')->willReturn($expectedQuery);

        $catalogSearchAdvanced = $this->createPartialMock(
            Advanced::class,
            ['addFilters', '__wakeup']
        );
        $catalogSearchAdvanced->expects($this->once())->method('addFilters')->willReturnCallback(
            function ($added) use (&$filters) {
                $filters = $added;
            }
        );

        $objectManager = new ObjectManager($this);
        $context = $objectManager->getObject(
            Context::class,
            ['view' => $view, 'request' => $request]
        );

        /** @var Result $instance */
        $instance = $objectManager->getObject(
            Result::class,
            ['context' => $context, 'catalogSearchAdvanced' => $catalogSearchAdvanced]
        );
        $instance->execute();
    }

    /**
     * Test url set on exception scenario
     *
     * @return void
     */
    public function testUrlSetOnException()
    {
        $redirectResultMock = $this->createMock(Redirect::class);
        $redirectResultMock->expects($this->once())
            ->method('setUrl');

        $redirectFactoryMock = $this->getMockBuilder(RedirectFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $redirectFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($redirectResultMock);

        $catalogSearchAdvanced = $this->createPartialMock(
            Advanced::class,
            ['addFilters']
        );

        $catalogSearchAdvanced->expects($this->once())->method('addFilters')->willThrowException(
            new LocalizedException(
                new Phrase("Test Exception")
            )
        );

        $responseMock = $this->createMock(Response::class);
        $requestMock = $this->createPartialMock(
            Http::class,
            ['getQueryValue']
        );
        $requestMock->expects($this->any())->method('getQueryValue')->willReturn(['key' => 'value']);

        $redirectMock = $this->getMockForAbstractClass(RedirectInterface::class);
        $redirectMock->expects($this->any())->method('error')->with('urlstring');

        $messageManagerMock = $this->createMock(Manager::class);

        $eventManagerMock = $this->getMockForAbstractClass(ManagerInterface::class);

        $contextMock = $this->createMock(Context::class);
        $contextMock->expects($this->any())
            ->method('getRequest')
            ->willReturn($requestMock);
        $contextMock->expects($this->any())
            ->method('getResponse')
            ->willReturn($responseMock);
        $contextMock->expects($this->any())
            ->method('getRedirect')
            ->willReturn($redirectMock);
        $contextMock->expects($this->any())
            ->method('getMessageManager')
            ->willReturn($messageManagerMock);
        $contextMock->expects($this->any())
            ->method('getEventManager')
            ->willReturn($eventManagerMock);
        $contextMock->expects($this->any())
            ->method('getResultRedirectFactory')
            ->willReturn($redirectFactoryMock);

        $urlMock = $this->createMock(Url::class);
        $urlMock->expects($this->once())
            ->method('addQueryParams')
            ->willReturnSelf();
        $urlMock->expects($this->once())
            ->method('getUrl')
            ->willReturn("urlstring");

        $urlFactoryMock = $this->createMock(UrlFactory::class);
        $urlFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($urlMock);

        $objectManager = new ObjectManager($this);

        /** @var Result $instance */
        $instance = $objectManager->getObject(
            Result::class,
            [
                'context'               => $contextMock,
                'catalogSearchAdvanced' => $catalogSearchAdvanced,
                'urlFactory'            => $urlFactoryMock
            ]
        );
        $this->assertEquals($redirectResultMock, $instance->execute());
    }

    /**
     * Test no result handle scenario
     *
     * @return void
     */
    public function testNoResultsHandle()
    {
        $expectedQuery = 'notExistTerm';

        $update = $this->createPartialMock(Merge::class, ['getHandles']);
        $update->expects($this->once())->method('getHandles')->willReturn([]);

        $layout = $this->getMockBuilder(Layout::class)
            ->addMethods(['getUpdate'])
            ->disableOriginalConstructor()
            ->getMock();
        $layout->expects($this->once())->method('getUpdate')->willReturn($update);

        $page = $this->createPartialMock(Page::class, ['initLayout']);

        $view = $this->createPartialMock(
            View::class,
            ['loadLayout', 'renderLayout', 'getPage', 'getLayout']
        );

        $view->expects($this->once())->method('loadLayout')
            ->with([Result::DEFAULT_NO_RESULT_HANDLE]);

        $view->expects($this->once())->method('getPage')->willReturn($page);
        $view->expects($this->once())->method('getLayout')->willReturn($layout);

        $request = $this->getMockBuilder(Request::class)
            ->addMethods(['getQueryValue'])
            ->disableOriginalConstructor()
            ->getMock();
        $request->expects($this->once())->method('getQueryValue')->willReturn($expectedQuery);

        $catalogSearchAdvanced = $this->createPartialMock(
            Advanced::class,
            ['addFilters', '__wakeup', 'getProductCollection']
        );

        $objectManager = new ObjectManager($this);
        $context = $objectManager->getObject(
            Context::class,
            ['view' => $view, 'request' => $request]
        );

        /** @var Result $instance */
        $instance = $objectManager->getObject(
            Result::class,
            ['context' => $context, 'catalogSearchAdvanced' => $catalogSearchAdvanced]
        );
        $instance->execute();
    }
}
