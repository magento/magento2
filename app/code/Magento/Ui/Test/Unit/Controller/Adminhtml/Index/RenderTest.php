<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Test\Unit\Controller\Adminhtml\Index;

use Magento\Ui\Controller\Adminhtml\Index\Render;
use Magento\Ui\Model\UiComponentTypeResolver;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RenderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Render
     */
    private $render;

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

        $this->render = new Render($this->contextMock, $this->uiFactoryMock, $this->uiComponentTypeResolverMock);
    }

    public function testExecuteAjaxRequest()
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

        $this->render->executeAjaxRequest();
    }

    /**
     * @param array $dataProviderConfig
     * @param bool|null $isAllowed
     * @param int $authCallCount
     * @dataProvider executeAjaxRequestWithoutPermissionsDataProvider
     */
    public function testExecuteAjaxRequestWithoutPermissions(array $dataProviderConfig, $isAllowed, $authCallCount = 1)
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

        $this->render->executeAjaxRequest();
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
