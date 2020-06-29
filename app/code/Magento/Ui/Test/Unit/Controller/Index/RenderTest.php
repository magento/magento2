<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Ui\Test\Unit\Controller\Index;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Helper\Data;
use Magento\Backend\Model\Session;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\Http as HttpResponse;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Escaper;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProviderInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Ui\Controller\Index\Render;
use Magento\Ui\Model\UiComponentTypeResolver;
use Laminas\Http\AbstractMessage;
use Laminas\Http\Response;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class RenderTest extends TestCase
{
    /**
     * @var Render
     */
    private $render;

    /**
     * @var RequestInterface|MockObject
     */
    private $requestMock;

    /**
     * @var ResponseInterface|MockObject
     */
    private $responseMock;

    /**
     * @var RedirectInterface|MockObject
     */
    private $redirectMock;

    /**
     * @var UiComponentFactory|MockObject
     */
    private $uiFactoryMock;

    /**
     * @var Context|MockObject
     */
    private $contextMock;

    /**
     * @var AuthorizationInterface|MockObject
     */
    private $authorizationMock;

    /**
     * @var Session|MockObject
     */
    private $sessionMock;

    /**
     * @var ActionFlag|MockObject
     */
    private $actionFlagMock;

    /**
     * @var Data|MockObject
     */
    private $helperMock;

    /**
     * @var ContextInterface|MockObject
     */
    private $uiComponentContextMock;

    /**
     * @var DataProviderInterface|MockObject
     */
    private $dataProviderMock;

    /**
     * @var UiComponentInterface|MockObject
     */
    private $uiComponentMock;

    /**
     * @var MockObject|UiComponentTypeResolver
     */
    private $uiComponentTypeResolverMock;

    /**
     * @var JsonFactory|MockObject
     */
    private $resultJsonFactoryMock;

    /**
     * @var LoggerInterface|MockObject
     */
    private $loggerMock;

    /**
     * @var Escaper|MockObject
     */
    private $escaperMock;

    protected function setUp(): void
    {
        $this->requestMock = $this->getMockBuilder(Http::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->redirectMock = $this->getMockBuilder(RedirectInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->uiFactoryMock = $this->getMockBuilder(UiComponentFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->authorizationMock = $this->getMockBuilder(AuthorizationInterface::class)
            ->getMockForAbstractClass();
        $this->sessionMock = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->actionFlagMock = $this->getMockBuilder(ActionFlag::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->helperMock = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->uiComponentContextMock = $this->getMockForAbstractClass(
            ContextInterface::class
        );
        $this->dataProviderMock = $this->getMockForAbstractClass(
            DataProviderInterface::class
        );
        $this->uiComponentMock = $this->getMockForAbstractClass(
            UiComponentInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['render']
        );

        $this->resultJsonFactoryMock = $this->getMockBuilder(
            JsonFactory::class
        )
            ->disableOriginalConstructor()
            ->getMock();

        $this->loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);

        $this->contextMock->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->requestMock);
        $this->contextMock->expects($this->any())
            ->method('getResponse')
            ->willReturn($this->responseMock);
        $this->contextMock->expects($this->any())
            ->method('getAuthorization')
            ->willReturn($this->authorizationMock);
        $this->contextMock->expects($this->any())
            ->method('getSession')
            ->willReturn($this->sessionMock);
        $this->contextMock->expects($this->any())
            ->method('getActionFlag')
            ->willReturn($this->actionFlagMock);
        $this->contextMock->expects($this->any())
            ->method('getHelper')
            ->willReturn($this->helperMock);
        $this->uiComponentContextMock->expects($this->once())
            ->method('getDataProvider')
            ->willReturn($this->dataProviderMock);
        $this->uiComponentTypeResolverMock = $this->getMockBuilder(UiComponentTypeResolver::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->escaperMock = $this->createMock(Escaper::class);
        $this->escaperMock->expects($this->any())
            ->method('escapeHtml')
            ->willReturnArgument(0);

        $this->render = new Render(
            $this->contextMock,
            $this->uiFactoryMock,
            $this->uiComponentTypeResolverMock,
            $this->resultJsonFactoryMock,
            $this->escaperMock,
            $this->loggerMock
        );
    }

    public function testExecuteException()
    {
        $name = 'test-name';
        $renderedData = '<html>data</html>';

        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->with('namespace')
            ->willReturn($name);
        $this->requestMock->expects($this->any())
            ->method('getParams')
            ->willReturn([]);
        $this->responseMock->expects($this->once())
            ->method('appendBody')
            ->willThrowException(new \Exception('exception'));

        $jsonResultMock = $this->getMockBuilder(Json::class)
            ->disableOriginalConstructor()
            ->setMethods(['setData'])
            ->getMock();

        $this->resultJsonFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($jsonResultMock);

        $jsonResultMock->expects($this->once())
            ->method('setData')
            ->willReturnSelf();

        $this->loggerMock->expects($this->once())
            ->method('critical')
            ->willReturnSelf();

        $this->dataProviderMock->expects($this->once())
            ->method('getConfigData')
            ->willReturn([]);

        $this->uiComponentMock->expects($this->once())
            ->method('render')
            ->willReturn($renderedData);
        $this->uiComponentMock->expects($this->once())
            ->method('getChildComponents')
            ->willReturn([]);
        $this->uiComponentMock->expects($this->once())
            ->method('getContext')
            ->willReturn($this->uiComponentContextMock);
        $this->uiFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->uiComponentMock);

        $this->render->execute();
    }

    public function testExecute()
    {
        $name = 'test-name';
        $renderedData = '<html>data</html>';

        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->with('namespace')
            ->willReturn($name);
        $this->requestMock->expects($this->any())
            ->method('getParams')
            ->willReturn([]);
        $this->responseMock->expects($this->once())
            ->method('appendBody')
            ->with($renderedData);
        $this->dataProviderMock->expects($this->once())
            ->method('getConfigData')
            ->willReturn([]);

        $this->uiComponentMock->expects($this->once())
            ->method('render')
            ->willReturn($renderedData);
        $this->uiComponentMock->expects($this->once())
            ->method('getChildComponents')
            ->willReturn([]);
        $this->uiComponentMock->expects($this->any())
            ->method('getContext')
            ->willReturn($this->uiComponentContextMock);
        $this->uiFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->uiComponentMock);
        $this->uiComponentTypeResolverMock->expects($this->once())
            ->method('resolve')
            ->with($this->uiComponentContextMock)
            ->willReturn('application/json');
        $this->responseMock->expects($this->once())->method('setHeader')
            ->with('Content-Type', 'application/json', true);

        $this->render->execute();
    }

    /**
     * @param array $dataProviderConfig
     * @param bool|null $isAllowed
     * @param int $authCallCount
     * @dataProvider executeAjaxRequestWithoutPermissionsDataProvider
     */
    public function testExecuteWithoutPermissions(array $dataProviderConfig, $isAllowed, $authCallCount = 1)
    {
        $name = 'test-name';
        $renderedData = '<html>data</html>';

        if (false === $isAllowed) {
            $jsonResultMock = $this->getMockBuilder(Json::class)
                ->disableOriginalConstructor()
                ->setMethods(['setStatusHeader', 'setData'])
                ->getMock();

            $jsonResultMock->expects($this->at(0))
                ->method('setStatusHeader')
                ->with(
                    Response::STATUS_CODE_403,
                    AbstractMessage::VERSION_11,
                    'Forbidden'
                )
                ->willReturnSelf();

            $jsonResultMock->expects($this->at(1))
                ->method('setData')
                ->with(
                    [
                        'error' => 'Forbidden',
                        'errorcode' => 403
                    ]
                )
                ->willReturnSelf();

            $this->resultJsonFactoryMock->expects($this->any())
                ->method('create')
                ->willReturn($jsonResultMock);
        }

        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->with('namespace')
            ->willReturn($name);
        $this->requestMock->expects($this->any())
            ->method('getParams')
            ->willReturn([]);
        if ($isAllowed === false) {
            $this->requestMock->expects($this->once())
                ->method('isAjax')
                ->willReturn(true);
        }
        $this->responseMock->expects($this->never())
            ->method('setRedirect');
        $this->responseMock->expects($this->any())
            ->method('appendBody')
            ->with($renderedData);

        $this->dataProviderMock->expects($this->once())
            ->method('getConfigData')
            ->willReturn($dataProviderConfig);

        $this->authorizationMock->expects($this->exactly($authCallCount))
            ->method('isAllowed')
            ->with(isset($dataProviderConfig['aclResource']) ? $dataProviderConfig['aclResource'] : null)
            ->willReturn($isAllowed);

        $this->uiComponentMock->expects($this->any())
            ->method('render')
            ->willReturn($renderedData);
        $this->uiComponentMock->expects($this->any())
            ->method('getChildComponents')
            ->willReturn([]);
        $this->uiComponentMock->expects($this->any())
            ->method('getContext')
            ->willReturn($this->uiComponentContextMock);
        $this->uiFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->uiComponentMock);

        $this->render->execute();
    }

    /**
     * @return array
     */
    public function executeAjaxRequestWithoutPermissionsDataProvider()
    {
        $aclResource = 'Magento_Test::index_index';
        return [
            [
                'dataProviderConfig' => ['aclResource' => $aclResource],
                'isAllowed' => true
            ],
            [
                'dataProviderConfig' => ['aclResource' => $aclResource],
                'isAllowed' => false
            ],
            [
                'dataProviderConfig' => [],
                'isAllowed' => null,
                'authCallCount' => 0
            ],
        ];
    }
}
