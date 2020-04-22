<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Rss\Test\Unit\Controller\Adminhtml\Feed;

use Magento\Backend\Model\UrlInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Rss\DataProviderInterface;
use Magento\Framework\Exception\RuntimeException;
use Magento\Framework\Phrase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Rss\Controller\Adminhtml\Feed\Index as AdminIndex;
use Magento\Rss\Model\Rss;
use Magento\Rss\Model\RssFactory;
use Magento\Rss\Model\RssManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class IndexTest extends TestCase
{
    /**
     * @var AdminIndex
     */
    protected $controller;

    /**
     * @var Context|MockObject
     */
    protected $context;

    /**
     * @var RssManager|MockObject
     */
    protected $rssManager;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    protected $scopeConfigInterface;

    /**
     * @var MockObject
     */
    protected $rssFactory;

    /**
     * @var MockObject
     */
    protected $response;

    protected function setUp(): void
    {
        $this->rssManager = $this->createPartialMock(RssManager::class, ['getProvider']);
        $this->scopeConfigInterface = $this->createMock(ScopeConfigInterface::class);
        $this->rssFactory = $this->createPartialMock(RssFactory::class, ['create']);

        $request = $this->createMock(RequestInterface::class);
        $request->expects($this->once())->method('getParam')->with('type')->will($this->returnValue('rss_feed'));

        $this->response = $this->getMockBuilder(ResponseInterface::class)
            ->setMethods(['setHeader', 'setBody', 'sendResponse'])
            ->disableOriginalConstructor()->getMock();

        $objectManagerHelper = new ObjectManagerHelper($this);
        $controllerArguments = $objectManagerHelper->getConstructArguments(
            AdminIndex::class,
            [
                'rssManager' => $this->rssManager,
                'scopeConfig' => $this->scopeConfigInterface,
                'rssFactory' => $this->rssFactory,
                'request' => $request,
                'response' => $this->response
            ]
        );
        $objectManager = $controllerArguments['context']->getObjectManager();
        $urlInterface = $this->createMock(UrlInterface::class);
        $objectManager->expects($this->at(0))->method('get')->with(UrlInterface::class)
            ->will($this->returnValue($urlInterface));
        $this->controller = $objectManagerHelper->getObject(
            AdminIndex::class,
            $controllerArguments
        );
    }

    public function testExecute()
    {
        $this->scopeConfigInterface->expects($this->once())->method('getValue')->will($this->returnValue(true));
        $dataProvider = $this->createMock(DataProviderInterface::class);
        $dataProvider->expects($this->once())->method('isAllowed')->will($this->returnValue(true));

        $rssModel = $this->createPartialMock(Rss::class, ['setDataProvider', 'createRssXml']);
        $rssModel->expects($this->once())->method('setDataProvider')->will($this->returnSelf());
        $rssModel->expects($this->once())->method('createRssXml')->will($this->returnValue(''));

        $this->response->expects($this->once())->method('setHeader')->will($this->returnSelf());
        $this->response->expects($this->once())->method('setBody')->will($this->returnSelf());

        $this->rssFactory->expects($this->once())->method('create')->will($this->returnValue($rssModel));

        $this->rssManager->expects($this->once())->method('getProvider')->will($this->returnValue($dataProvider));
        $this->controller->execute();
    }

    public function testExecuteWithException()
    {
        $this->scopeConfigInterface->expects($this->once())->method('getValue')->will($this->returnValue(true));
        $dataProvider = $this->createMock(DataProviderInterface::class);
        $dataProvider->expects($this->once())->method('isAllowed')->will($this->returnValue(true));

        $rssModel = $this->createPartialMock(Rss::class, ['setDataProvider', 'createRssXml']);
        $rssModel->expects($this->once())->method('setDataProvider')->will($this->returnSelf());

        $exceptionMock = new RuntimeException(
            new Phrase('Any message')
        );

        $rssModel->expects($this->once())->method('createRssXml')->will(
            $this->throwException($exceptionMock)
        );

        $this->response->expects($this->once())->method('setHeader')->will($this->returnSelf());
        $this->rssFactory->expects($this->once())->method('create')->will($this->returnValue($rssModel));
        $this->rssManager->expects($this->once())->method('getProvider')->will($this->returnValue($dataProvider));

        $this->expectException(RuntimeException::class);
        $this->controller->execute();
    }
}
