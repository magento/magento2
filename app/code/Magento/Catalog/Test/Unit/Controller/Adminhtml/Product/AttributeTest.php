<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Controller\Adminhtml\Product;

use Magento\Backend\App\Action\Context;
use Magento\Catalog\Controller\Adminhtml\Product\Attribute;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Cache\FrontendInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\View\Result\PageFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AttributeTest extends TestCase
{
    use MockCreationTrait;
    /**
     * @var ObjectManagerHelper
     */
    protected $objectManager;

    /**
     * @var Context|MockObject
     */
    protected $contextMock;

    /**
     * @var Registry|MockObject
     */
    protected $coreRegistryMock;

    /**
     * @var FrontendInterface|MockObject
     */
    protected $attributeLabelCacheMock;

    /**
     * @var PageFactory|MockObject
     */
    protected $resultPageFactoryMock;

    /**
     * @var RequestInterface|MockObject
     */
    protected $requestMock;

    /**
     * @var ResultFactory|MockObject
     */
    protected $resultFactoryMock;

    /**
     * @var ManagerInterface|MockObject
     */
    protected $messageManager;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManagerHelper($this);
        $this->contextMock = $this->createMock(Context::class);
        $this->attributeLabelCacheMock = $this->createMock(FrontendInterface::class);
        $this->coreRegistryMock = $this->createMock(Registry::class);
        $this->resultPageFactoryMock = $this->createMock(PageFactory::class);
        $this->requestMock = $this->createPartialMockWithReflection(
            RequestInterface::class,
            [
                'getPostValue', 'getParam', 'has',
                'getModuleName', 'setModuleName', 'getActionName', 'setActionName',
                'getCookie', 'getDistroBaseUrl', 'getRequestUri', 'getScheme',
                'setParams', 'getParams', 'isSecure'
            ]
        );
        $this->requestMock->method('getModuleName')->willReturn('catalog');
        $this->requestMock->method('setModuleName')->willReturnSelf();
        $this->requestMock->method('getActionName')->willReturn('attribute');
        $this->requestMock->method('setActionName')->willReturnSelf();
        $this->requestMock->method('getCookie')->willReturn(null);
        $this->requestMock->method('getDistroBaseUrl')->willReturn('');
        $this->requestMock->method('getRequestUri')->willReturn('/');
        $this->requestMock->method('getScheme')->willReturn('http');
        $this->requestMock->method('setParams')->willReturnSelf();
        $this->requestMock->method('getParams')->willReturn([]);
        $this->requestMock->method('isSecure')->willReturn(false);
        $this->resultFactoryMock = $this->createMock(ResultFactory::class);
        $this->messageManager = $this->createMock(ManagerInterface::class);

        $this->contextMock->method('getRequest')->willReturn($this->requestMock);
        $this->contextMock->method('getResultFactory')->willReturn($this->resultFactoryMock);
        $this->contextMock
            ->method('getMessageManager')
            ->willReturn($this->messageManager);
    }

    /**
     * @return Attribute
     */
    protected function getModel()
    {
        return $this->objectManager->getObject(Attribute::class, [
            'context' => $this->contextMock,
            'attributeLabelCache' => $this->attributeLabelCacheMock,
            'coreRegistry' => $this->coreRegistryMock,
            'resultPageFactory' => $this->resultPageFactoryMock,
        ]);
    }

    public function testDispatch()
    {
        $this->markTestSkipped('Should be dispatched in parent');
    }
}
