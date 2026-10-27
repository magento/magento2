<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Theme\Test\Unit\Controller\Adminhtml\Design\Config;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\ScopeInterface;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\App\ScopeResolverPool;
use Magento\Framework\App\ScopeValidatorInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\View\Page\Config;
use Magento\Framework\View\Page\Title;
use Magento\Framework\View\Result\PageFactory;
use Magento\Theme\Controller\Adminhtml\Design\Config\Edit;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EditTest extends TestCase
{
    /**
     * @var Edit
     */
    protected $controller;

    /**
     * @var Context|MockObject
     */
    protected $context;

    /**
     * @var Page|MockObject
     */
    protected $resultPage;

    /**
     * @var Redirect|MockObject
     */
    protected $resultRedirect;

    /**
     * @var ScopeResolverPool|MockObject
     */
    protected $scopeResolverPool;

    /**
     * @var ScopeValidatorInterface|MockObject
     */
    protected $scopeValidator;

    /**
     * @var Http|MockObject
     */
    protected $request;

    protected function setUp(): void
    {
        $this->initContext();
        $resultPageFactory = $this->initResultPage();

        $this->scopeValidator = $this->createMock(ScopeValidatorInterface::class);

        $this->scopeResolverPool = $this->createMock(ScopeResolverPool::class);

        $this->controller = new Edit(
            $this->context,
            $resultPageFactory,
            $this->scopeValidator,
            $this->scopeResolverPool
        );
    }

    protected function initContext()
    {
        $this->request = $this->createMock(Http::class);

        $this->context = $this->createMock(Context::class);
        $this->context->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->request);

        $this->resultRedirect = $this->createMock(Redirect::class);

        $resultRedirectFactory = $this->getMockBuilder(RedirectFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $resultRedirectFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->resultRedirect);
        $this->context->expects($this->any())
            ->method('getResultRedirectFactory')
            ->willReturn($resultRedirectFactory);
    }

    /**
     * @return PageFactory|MockObject
     */
    protected function initResultPage()
    {
        $this->resultPage = $this->createMock(Page::class);

        $resultPageFactory = $this->createMock(PageFactory::class);
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

        $pageTitle = $this->createMock(Title::class);
        $pageTitle->expects($this->once())
            ->method('prepend')
            ->with(__('%1', $scopeName))
            ->willReturnSelf();

        $pageConfig = $this->createMock(Config::class);
        $pageConfig->expects($this->once())
            ->method('getTitle')
            ->willReturn($pageTitle);

        $scopeObject = $this->createMock(ScopeInterface::class);
        $scopeObject->expects($this->once())
            ->method('getName')
            ->willReturn($scopeName);

        $scopeResolver = $this->createMock(ScopeResolverInterface::class);
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

        $pageTitle = $this->createMock(Title::class);
        $pageTitle->expects($this->once())
            ->method('prepend')
            ->with(__('%1', $scopeName))
            ->willReturnSelf();

        $pageConfig = $this->createMock(Config::class);
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
