<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Rss\Test\Unit\Controller\Feed;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Zend\Feed\Writer\Exception\InvalidArgumentException;

/**
 * Class IndexTest
 * @package Magento\Rss\Controller\Feed
 */
class IndexTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Rss\Controller\Feed\Index
     */
    protected $controller;

    /**
     * @var \Magento\Rss\Model\RssManager|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $rssManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $scopeConfigInterface;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $rssFactory;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $response;

    protected function setUp(): void
    {
        $this->rssManager = $this->createPartialMock(\Magento\Rss\Model\RssManager::class, ['getProvider']);
        $this->scopeConfigInterface = $this->createMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $this->rssFactory = $this->createPartialMock(\Magento\Rss\Model\RssFactory::class, ['create']);

        $request = $this->createMock(\Magento\Framework\App\RequestInterface::class);
        $request->expects($this->once())->method('getParam')->with('type')->willReturn('rss_feed');

        $this->response = $this->getMockBuilder(\Magento\Framework\App\ResponseInterface::class)
            ->setMethods(['setHeader', 'setBody', 'sendResponse'])
            ->disableOriginalConstructor()->getMock();

        $objectManagerHelper = new ObjectManagerHelper($this);

        $this->controller = $objectManagerHelper->getObject(
            \Magento\Rss\Controller\Feed\Index::class,
            [
                'rssManager' => $this->rssManager,
                'scopeConfig' => $this->scopeConfigInterface,
                'rssFactory' => $this->rssFactory,
                'request' => $request,
                'response' => $this->response
            ]
        );
    }

    public function testExecute()
    {
        $this->scopeConfigInterface->expects($this->once())->method('getValue')->willReturn(true);
        $dataProvider = $this->createMock(\Magento\Framework\App\Rss\DataProviderInterface::class);
        $dataProvider->expects($this->once())->method('isAllowed')->willReturn(true);
        $dataProvider->expects($this->once())->method('isAuthRequired')->willReturn(false);

        $rssModel = $this->createPartialMock(\Magento\Rss\Model\Rss::class, ['setDataProvider', 'createRssXml']);
        $rssModel->expects($this->once())->method('setDataProvider')->willReturnSelf();
        $rssModel->expects($this->once())->method('createRssXml')->willReturn('');

        $this->response->expects($this->once())->method('setHeader')->willReturnSelf();
        $this->response->expects($this->once())->method('setBody')->willReturnSelf();

        $this->rssFactory->expects($this->once())->method('create')->willReturn($rssModel);

        $this->rssManager->expects($this->once())->method('getProvider')->willReturn($dataProvider);
        $this->controller->execute();
    }

    public function testExecuteWithException()
    {
        $this->scopeConfigInterface->expects($this->once())->method('getValue')->willReturn(true);
        $dataProvider = $this->createMock(\Magento\Framework\App\Rss\DataProviderInterface::class);
        $dataProvider->expects($this->once())->method('isAllowed')->willReturn(true);

        $rssModel = $this->createPartialMock(\Magento\Rss\Model\Rss::class, ['setDataProvider', 'createRssXml']);
        $rssModel->expects($this->once())->method('setDataProvider')->willReturnSelf();

        $exceptionMock = new \Magento\Framework\Exception\RuntimeException(
            new \Magento\Framework\Phrase('Any message')
        );

        $rssModel->expects($this->once())->method('createRssXml')->will(
            $this->throwException($exceptionMock)
        );

        $this->response->expects($this->once())->method('setHeader')->willReturnSelf();
        $this->rssFactory->expects($this->once())->method('create')->willReturn($rssModel);
        $this->rssManager->expects($this->once())->method('getProvider')->willReturn($dataProvider);

        $this->expectException(\Magento\Framework\Exception\RuntimeException::class);
        $this->controller->execute();
    }
}
