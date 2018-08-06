<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Test\Unit\Controller\Advanced;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ResultTest extends \PHPUnit\Framework\TestCase
{
    public function testResultActionFiltersSetBeforeLoadLayout()
    {
        $filters = null;
        $expectedQuery = 'filtersData';

        $view = $this->createPartialMock(\Magento\Framework\App\View::class, ['loadLayout', 'renderLayout']);
        $view->expects($this->once())->method('loadLayout')->will(
            $this->returnCallback(
                function () use (&$filters, $expectedQuery) {
                    $this->assertEquals($expectedQuery, $filters);
                }
            )
        );

        $request = $this->createPartialMock(\Magento\Framework\App\Console\Request::class, ['getQueryValue']);
        $request->expects($this->once())->method('getQueryValue')->will($this->returnValue($expectedQuery));

        $catalogSearchAdvanced = $this->createPartialMock(
            \Magento\CatalogSearch\Model\Advanced::class,
            ['addFilters', '__wakeup']
        );
        $catalogSearchAdvanced->expects($this->once())->method('addFilters')->will(
            $this->returnCallback(
                function ($added) use (&$filters) {
                    $filters = $added;
                }
            )
        );

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $context = $objectManager->getObject(
            \Magento\Framework\App\Action\Context::class,
            ['view' => $view, 'request' => $request]
        );

        /** @var \Magento\CatalogSearch\Controller\Advanced\Result $instance */
        $instance = $objectManager->getObject(
            \Magento\CatalogSearch\Controller\Advanced\Result::class,
            ['context' => $context, 'catalogSearchAdvanced' => $catalogSearchAdvanced]
        );
        $instance->execute();
    }

    public function testUrlSetOnException()
    {
        $redirectResultMock = $this->createMock(\Magento\Framework\Controller\Result\Redirect::class);
        $redirectResultMock->expects($this->once())
            ->method('setUrl');

        $redirectFactoryMock = $this->getMockBuilder(\Magento\Framework\Controller\Result\RedirectFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $redirectFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($redirectResultMock);

        $catalogSearchAdvanced = $this->createPartialMock(
            \Magento\CatalogSearch\Model\Advanced::class,
            ['addFilters']
        );

        $catalogSearchAdvanced->expects($this->once())->method('addFilters')->will(
            $this->throwException(new \Magento\Framework\Exception\LocalizedException(
                new \Magento\Framework\Phrase("Test Exception")
            ))
        );

        $responseMock = $this->createMock(\Magento\Framework\Webapi\Response::class);
        $requestMock = $this->createPartialMock(
            \Magento\Framework\App\Request\Http::class,
            ['getQueryValue']
        );
        $requestMock->expects($this->any())->method('getQueryValue')->willReturn(['key' => 'value']);

        $redirectMock = $this->createMock(\Magento\Framework\App\Response\RedirectInterface::class);
        $redirectMock->expects($this->any())->method('error')->with('urlstring');

        $messageManagerMock = $this->createMock(\Magento\Framework\Message\Manager::class);

        $eventManagerMock = $this->createMock(\Magento\Framework\Event\ManagerInterface::class);

        $contextMock = $this->createMock(\Magento\Framework\App\Action\Context::class);
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

        $urlMock = $this->createMock(\Magento\Framework\Url::class);
        $urlMock->expects($this->once())
            ->method('addQueryParams')
            ->willReturnSelf();
        $urlMock->expects($this->once())
            ->method('getUrl')
            ->willReturn("urlstring");

        $urlFactoryMock = $this->createMock(\Magento\Framework\UrlFactory::class);
        $urlFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($urlMock));

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        /** @var \Magento\CatalogSearch\Controller\Advanced\Result $instance */
        $instance = $objectManager->getObject(
            \Magento\CatalogSearch\Controller\Advanced\Result::class,
            ['context' => $contextMock,
            'catalogSearchAdvanced' => $catalogSearchAdvanced,
            'urlFactory' => $urlFactoryMock
            ]
        );
        $this->assertEquals($redirectResultMock, $instance->execute());
    }
}
