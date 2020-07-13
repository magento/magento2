<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Search\Test\Unit\Controller\Adminhtml\Ajax;

use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\Context as ActionContet;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\UrlInterface;
use Magento\Search\Controller\Ajax\Suggest;
use Magento\Search\Model\Autocomplete\Item;
use Magento\Search\Model\AutocompleteInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SuggestTest extends TestCase
{
    /** @var Suggest */
    private $controller;

    /** @var ObjectManagerHelper */
    private $objectManagerHelper;

    /** @var RequestInterface|MockObject */
    private $request;

    /** @var UrlInterface|MockObject */
    private $url;

    /** @var \Magento\Backend\App\Action\Context|MockObject */
    private $context;

    /** @var AutocompleteInterface|MockObject */
    private $autocomplete;

    /**
     * @var ResultFactory|MockObject
     */
    protected $resultFactoryMock;

    /**
     * @var Redirect|MockObject
     */
    protected $resultRedirectMock;

    /**
     * @var Json|MockObject
     */
    protected $resultJsonMock;

    protected function setUp(): void
    {
        $this->autocomplete = $this->getMockBuilder(AutocompleteInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getItems'])
            ->getMockForAbstractClass();
        $this->request = $this->getMockBuilder(RequestInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMockForAbstractClass();
        $this->url = $this->getMockBuilder(UrlInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getBaseUrl'])
            ->getMockForAbstractClass();
        $this->resultFactoryMock = $this->getMockBuilder(ResultFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultRedirectMock = $this->getMockBuilder(Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultJsonMock = $this->getMockBuilder(Json::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->context = $this->getMockBuilder(ActionContet::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->context->expects($this->atLeastOnce())
            ->method('getRequest')
            ->willReturn($this->request);
        $this->context->expects($this->any())
            ->method('getUrl')
            ->willReturn($this->url);
        $this->context->expects($this->any())
            ->method('getResultFactory')
            ->willReturn($this->resultFactoryMock);
        $this->resultFactoryMock->expects($this->any())
            ->method('create')
            ->willReturnMap(
                [
                    [ResultFactory::TYPE_REDIRECT, [], $this->resultRedirectMock],
                    [ResultFactory::TYPE_JSON, [], $this->resultJsonMock]
                ]
            );

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->controller = $this->objectManagerHelper->getObject(
            Suggest::class,
            [
                'context' => $this->context,
                'autocomplete' => $this->autocomplete
            ]
        );
    }

    public function testExecute()
    {
        $searchString = "simple";
        $firstItemMock =  $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->setMockClassName('FirstItem')
            ->setMethods(['toArray'])
            ->getMock();
        $secondItemMock =  $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->setMockClassName('SecondItem')
            ->setMethods(['toArray'])
            ->getMock();

        $this->request->expects($this->once())
            ->method('getParam')
            ->with('q')
            ->willReturn($searchString);

        $this->autocomplete->expects($this->once())
            ->method('getItems')
            ->willReturn([$firstItemMock, $secondItemMock]);

        $this->resultJsonMock->expects($this->once())
            ->method('setData')
            ->willReturnSelf();

        $this->assertSame($this->resultJsonMock, $this->controller->execute());
    }

    public function testExecuteEmptyQuery()
    {
        $url = 'some url';
        $searchString = '';

        $this->request->expects($this->once())
            ->method('getParam')
            ->with('q')
            ->willReturn($searchString);
        $this->url->expects($this->once())
            ->method('getBaseUrl')
            ->willReturn($url);
        $this->resultRedirectMock->expects($this->once())
            ->method('setUrl')
            ->with($url)
            ->willReturnSelf();

        $this->assertSame($this->resultRedirectMock, $this->controller->execute());
    }
}
