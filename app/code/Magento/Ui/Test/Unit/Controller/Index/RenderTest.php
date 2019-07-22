<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Ui\Test\Unit\Controller\Index;

use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Escaper;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Controller\Index\Render;
use Magento\Ui\Model\UiComponentTypeResolver;
use Zend\Http\AbstractMessage;
use Zend\Http\Response;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class RenderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Render
     */
    private $render;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $responseMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $uiFactoryMock;

    /**
     * @var \Magento\Backend\App\Action\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $contextMock;

    /**
     * @var \Magento\Framework\AuthorizationInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $authorizationMock;

    /**
     * @var \Magento\Backend\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sessionMock;

    /**
     * @var \Magento\Framework\App\ActionFlag|\PHPUnit_Framework_MockObject_MockObject
     */
    private $actionFlagMock;

    /**
     * @var \Magento\Backend\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    private $helperMock;

    /**
     * @var ContextInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $uiComponentContextMock;

    /**
     * @var \Magento\Framework\View\Element\UiComponent\DataProvider\DataProviderInterface|
     *      \PHPUnit_Framework_MockObject_MockObject
     */
    private $dataProviderMock;

    /**
     * @var \Magento\Framework\View\Element\UiComponentInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $uiComponentMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|UiComponentTypeResolver
     */
    private $uiComponentTypeResolverMock;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultJsonFactoryMock;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $loggerMock;

    /**
     * @var Escaper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $escaperMock;

    protected function setUp()
    {
        $this->requestMock = $this->getMockBuilder(\Magento\Framework\App\Request\Http::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->responseMock = $this->getMockBuilder(\Magento\Framework\App\Response\Http::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->contextMock = $this->getMockBuilder(\Magento\Backend\App\Action\Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->uiFactoryMock = $this->getMockBuilder(\Magento\Framework\View\Element\UiComponentFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->authorizationMock = $this->getMockBuilder(\Magento\Framework\AuthorizationInterface::class)
            ->getMockForAbstractClass();
        $this->sessionMock = $this->getMockBuilder(\Magento\Backend\Model\Session::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->actionFlagMock = $this->getMockBuilder(\Magento\Framework\App\ActionFlag::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->helperMock = $this->getMockBuilder(\Magento\Backend\Helper\Data::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->uiComponentContextMock = $this->getMockForAbstractClass(
            ContextInterface::class
        );
        $this->dataProviderMock = $this->getMockForAbstractClass(
            \Magento\Framework\View\Element\UiComponent\DataProvider\DataProviderInterface::class
        );
        $this->uiComponentMock = $this->getMockForAbstractClass(
            \Magento\Framework\View\Element\UiComponentInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['render']
        );

        $this->resultJsonFactoryMock = $this->getMockBuilder(
            \Magento\Framework\Controller\Result\JsonFactory::class
        )
            ->disableOriginalConstructor()
            ->getMock();

        $this->loggerMock = $this->getMockForAbstractClass(\Psr\Log\LoggerInterface::class);

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

        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->render = $this->objectManagerHelper->getObject(
            \Magento\Ui\Controller\Index\Render::class,
            [
                'context' => $this->contextMock,
                'uiComponentFactory' => $this->uiFactoryMock,
                'contentTypeResolver' => $this->uiComponentTypeResolverMock,
                'resultJsonFactory' => $this->resultJsonFactoryMock,
                'logger' => $this->loggerMock,
                'escaper' => $this->escaperMock,
            ]
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
                ->with([
                    'error' => 'Forbidden',
                    'errorcode' => 403
                ])
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
