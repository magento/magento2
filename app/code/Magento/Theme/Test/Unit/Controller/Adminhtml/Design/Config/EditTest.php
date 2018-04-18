<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Test\Unit\Controller\Adminhtml\Design\Config;

use Magento\Theme\Controller\Adminhtml\Design\Config\Edit;

class EditTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Edit
     */
    protected $controller;

    /**
     * @var \Magento\Backend\App\Action\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    /**
     * @var \Magento\Backend\Model\View\Result\Page|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultPage;

    /**
     * @var \Magento\Framework\Controller\Result\Redirect|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultRedirect;

    /**
     * @var \Magento\Framework\App\ScopeResolverPool|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeResolverPool;

    /**
     * @var \Magento\Framework\App\ScopeValidatorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeValidator;

    /**
     * @var \Magento\Framework\App\Request\Http|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $request;

    protected function setUp()
    {
        $this->initContext();
        $resultPageFactory = $this->initResultPage();

        $this->scopeValidator = $this->getMockBuilder('Magento\Framework\App\ScopeValidatorInterface')
            ->getMockForAbstractClass();

        $this->scopeResolverPool = $this->getMockBuilder('Magento\Framework\App\ScopeResolverPool')
            ->disableOriginalConstructor()
            ->getMock();

        $this->controller = new Edit(
            $this->context,
            $resultPageFactory,
            $this->scopeValidator,
            $this->scopeResolverPool
        );
    }

    protected function initContext()
    {
        $this->request = $this->getMockBuilder('Magento\Framework\App\Request\Http')
            ->disableOriginalConstructor()
            ->getMock();

        $this->context = $this->getMockBuilder('Magento\Backend\App\Action\Context')
            ->disableOriginalConstructor()
            ->getMock();
        $this->context->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->request);

        $this->resultRedirect = $this->getMockBuilder('Magento\Framework\Controller\Result\Redirect')
            ->disableOriginalConstructor()
            ->getMock();

        $resultRedirectFactory = $this->getMockBuilder('Magento\Framework\Controller\Result\RedirectFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $resultRedirectFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->resultRedirect);
        $this->context->expects($this->any())
            ->method('getResultRedirectFactory')
            ->willReturn($resultRedirectFactory);
    }

    /**
     * @return \Magento\Framework\View\Result\PageFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function initResultPage()
    {
        $this->resultPage = $this->getMockBuilder('Magento\Backend\Model\View\Result\Page')
            ->disableOriginalConstructor()
            ->getMock();

        $resultPageFactory = $this->getMockBuilder('Magento\Framework\View\Result\PageFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $resultPageFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->resultPage);
        return $resultPageFactory;
    }

    public function testScope()
    {
        $scope = 'websites';
        $scopeId = 1;
        $scopeName = 'Website Name';

        $this->request->expects($this->exactly(4))
            ->method('getParam')
            ->willReturnMap([
                ['scope', null, $scope],
                ['scope_id', null, $scopeId],
            ]);

        $this->scopeValidator->expects($this->once())
            ->method('isValidScope')
            ->with($scope, $scopeId)
            ->willReturn(true);

        $pageTitle = $this->getMockBuilder('Magento\Framework\View\Page\Title')
            ->disableOriginalConstructor()
            ->getMock();
        $pageTitle->expects($this->once())
            ->method('prepend')
            ->with(__('%1', $scopeName))
            ->willReturnSelf();

        $pageConfig = $this->getMockBuilder('Magento\Framework\View\Page\Config')
            ->disableOriginalConstructor()
            ->getMock();
        $pageConfig->expects($this->once())
            ->method('getTitle')
            ->willReturn($pageTitle);

        $scopeObject = $this->getMockBuilder('Magento\Framework\App\ScopeInterface')
            ->getMockForAbstractClass();
        $scopeObject->expects($this->once())
            ->method('getName')
            ->willReturn($scopeName);

        $scopeResolver = $this->getMockBuilder('Magento\Framework\App\ScopeResolverInterface')
            ->getMockForAbstractClass();
        $scopeResolver->expects($this->once())
            ->method('getScope')
            ->with($scopeId)
            ->willReturn($scopeObject);

        $this->scopeResolverPool->expects($this->once())
            ->method('get')
            ->with($scope)
            ->willReturn($scopeResolver);

        $this->resultPage->expects($this->once())
            ->method('setActiveMenu')
            ->with('Magento_Theme::design_config')
            ->willReturnSelf();
        $this->resultPage->expects($this->once())
            ->method('getConfig')
            ->willReturn($pageConfig);

        $this->assertSame($this->resultPage, $this->controller->execute());
    }

    public function testScopeDefault()
    {
        $scope = 'default';
        $scopeId = 0;
        $scopeName = 'Global';

        $this->request->expects($this->exactly(4))
            ->method('getParam')
            ->willReturnMap([
                ['scope', null, $scope],
                ['scope_id', null, $scopeId],
            ]);

        $this->scopeValidator->expects($this->once())
            ->method('isValidScope')
            ->with($scope, $scopeId)
            ->willReturn(true);

        $pageTitle = $this->getMockBuilder('Magento\Framework\View\Page\Title')
            ->disableOriginalConstructor()
            ->getMock();
        $pageTitle->expects($this->once())
            ->method('prepend')
            ->with(__('%1', $scopeName))
            ->willReturnSelf();

        $pageConfig = $this->getMockBuilder('Magento\Framework\View\Page\Config')
            ->disableOriginalConstructor()
            ->getMock();
        $pageConfig->expects($this->once())
            ->method('getTitle')
            ->willReturn($pageTitle);

        $this->resultPage->expects($this->once())
            ->method('setActiveMenu')
            ->with('Magento_Theme::design_config')
            ->willReturnSelf();
        $this->resultPage->expects($this->once())
            ->method('getConfig')
            ->willReturn($pageConfig);

        $this->assertSame($this->resultPage, $this->controller->execute());
    }

    public function testScopeRedirect()
    {
        $scope = 'test';
        $scopeId = 1;

        $this->request->expects($this->exactly(2))
            ->method('getParam')
            ->willReturnMap([
                ['scope', null, $scope],
                ['scope_id', null, $scopeId],
            ]);

        $this->scopeValidator->expects($this->once())
            ->method('isValidScope')
            ->with($scope, $scopeId)
            ->willReturn(false);

        $this->resultRedirect->expects($this->once())
            ->method('setPath')
            ->with('theme/design_config/')
            ->willReturnSelf();

        $this->assertSame($this->resultRedirect, $this->controller->execute());
    }
}
