<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Test\Unit\Controller\Adminhtml\Design\Config;

use Magento\Theme\Controller\Adminhtml\Design\Config\Edit;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EditTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Edit
     */
    protected $controller;

    /**
     * @var \Magento\Backend\App\Action\Context|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $context;

    /**
     * @var \Magento\Backend\Model\View\Result\Page|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $resultPage;

    /**
     * @var \Magento\Framework\Controller\Result\Redirect|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $resultRedirect;

    /**
     * @var \Magento\Framework\App\ScopeResolverPool|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $scopeResolverPool;

    /**
     * @var \Magento\Framework\App\ScopeValidatorInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $scopeValidator;

    /**
     * @var \Magento\Framework\App\Request\Http|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $request;

    protected function setUp(): void
    {
        $this->initContext();
        $resultPageFactory = $this->initResultPage();

        $this->scopeValidator = $this->getMockBuilder(\Magento\Framework\App\ScopeValidatorInterface::class)
            ->getMockForAbstractClass();

        $this->scopeResolverPool = $this->getMockBuilder(\Magento\Framework\App\ScopeResolverPool::class)
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
        $this->request = $this->getMockBuilder(\Magento\Framework\App\Request\Http::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->context = $this->getMockBuilder(\Magento\Backend\App\Action\Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->context->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->request);

        $this->resultRedirect = $this->getMockBuilder(\Magento\Framework\Controller\Result\Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();

        $resultRedirectFactory = $this->getMockBuilder(\Magento\Framework\Controller\Result\RedirectFactory::class)
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
     * @return \Magento\Framework\View\Result\PageFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function initResultPage()
    {
        $this->resultPage = $this->getMockBuilder(\Magento\Backend\Model\View\Result\Page::class)
            ->disableOriginalConstructor()
            ->getMock();

        $resultPageFactory = $this->getMockBuilder(\Magento\Framework\View\Result\PageFactory::class)
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

        $pageTitle = $this->getMockBuilder(\Magento\Framework\View\Page\Title::class)
            ->disableOriginalConstructor()
            ->getMock();
        $pageTitle->expects($this->once())
            ->method('prepend')
            ->with(__('%1', $scopeName))
            ->willReturnSelf();

        $pageConfig = $this->getMockBuilder(\Magento\Framework\View\Page\Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $pageConfig->expects($this->once())
            ->method('getTitle')
            ->willReturn($pageTitle);

        $scopeObject = $this->getMockBuilder(\Magento\Framework\App\ScopeInterface::class)
            ->getMockForAbstractClass();
        $scopeObject->expects($this->once())
            ->method('getName')
            ->willReturn($scopeName);

        $scopeResolver = $this->getMockBuilder(\Magento\Framework\App\ScopeResolverInterface::class)
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

        $pageTitle = $this->getMockBuilder(\Magento\Framework\View\Page\Title::class)
            ->disableOriginalConstructor()
            ->getMock();
        $pageTitle->expects($this->once())
            ->method('prepend')
            ->with(__('%1', $scopeName))
            ->willReturnSelf();

        $pageConfig = $this->getMockBuilder(\Magento\Framework\View\Page\Config::class)
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
