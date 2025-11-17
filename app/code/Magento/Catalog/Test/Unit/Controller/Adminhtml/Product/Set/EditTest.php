<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Controller\Adminhtml\Product\Set;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Page as ResultPage;
use Magento\Backend\Model\View\Result\Redirect as ResultRedirect;
use Magento\Catalog\Controller\Adminhtml\Product\Set\Edit;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Eav\Api\AttributeSetRepositoryInterface;
use Magento\Eav\Api\Data\AttributeSetInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Page\Config as PageConfig;
use Magento\Framework\View\Page\Title;
use Magento\Framework\View\Result\PageFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for Edit controller
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EditTest extends TestCase
{
    /**
     * @var Edit
     */
    private $controller;

    /**
     * @var Context|MockObject
     */
    private $contextMock;

    /**
     * @var Registry|MockObject
     */
    private $registryMock;

    /**
     * @var PageFactory|MockObject
     */
    private $resultPageFactoryMock;

    /**
     * @var AttributeSetRepositoryInterface|MockObject
     */
    private $attributeSetRepositoryMock;

    /**
     * @var RequestInterface|MockObject
     */
    private $requestMock;

    /**
     * @var ManagerInterface|MockObject
     */
    private $messageManagerMock;

    /**
     * @var RedirectFactory|MockObject
     */
    private $resultRedirectFactoryMock;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    private $objectManagerMock;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->contextMock = $this->createMock(Context::class);
        $this->registryMock = $this->createMock(Registry::class);
        $this->resultPageFactoryMock = $this->createMock(PageFactory::class);
        $this->attributeSetRepositoryMock = $this->getMockForAbstractClass(AttributeSetRepositoryInterface::class);
        $this->requestMock = $this->getMockForAbstractClass(RequestInterface::class);
        $this->messageManagerMock = $this->getMockForAbstractClass(ManagerInterface::class);
        $this->resultRedirectFactoryMock = $this->createMock(RedirectFactory::class);
        $this->objectManagerMock = $this->getMockForAbstractClass(ObjectManagerInterface::class);

        // Setup Product and ProductResource mocks for _setTypeId()
        $productResourceMock = $this->createMock(ProductResource::class);
        $productResourceMock->expects($this->any())
            ->method('getTypeId')
            ->willReturn(4);

        $productMock = $this->createMock(Product::class);
        $productMock->expects($this->any())
            ->method('getResource')
            ->willReturn($productResourceMock);

        $this->objectManagerMock->expects($this->any())
            ->method('create')
            ->with(Product::class)
            ->willReturn($productMock);

        $this->contextMock->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->requestMock);
        $this->contextMock->expects($this->any())
            ->method('getMessageManager')
            ->willReturn($this->messageManagerMock);
        $this->contextMock->expects($this->any())
            ->method('getResultRedirectFactory')
            ->willReturn($this->resultRedirectFactoryMock);
        $this->contextMock->expects($this->any())
            ->method('getObjectManager')
            ->willReturn($this->objectManagerMock);

        $this->controller = $this->objectManager->getObject(
            Edit::class,
            [
                'context' => $this->contextMock,
                'coreRegistry' => $this->registryMock,
                'resultPageFactory' => $this->resultPageFactoryMock,
                'attributeSetRepository' => $this->attributeSetRepositoryMock
            ]
        );
    }

    /**
     * Test constructor with null attribute set repository (uses ObjectManager fallback)
     *
     * @return void
     */
    public function testConstructorWithNullAttributeSetRepository(): void
    {
        // Create a mock for ObjectManager singleton
        $objectManagerMock = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $attributeSetRepositoryMock = $this->getMockForAbstractClass(AttributeSetRepositoryInterface::class);
        
        $objectManagerMock->expects($this->once())
            ->method('get')
            ->with(AttributeSetRepositoryInterface::class)
            ->willReturn($attributeSetRepositoryMock);

        // Set ObjectManager singleton for the test
        \Magento\Framework\App\ObjectManager::setInstance($objectManagerMock);

        // Create controller without attributeSetRepository parameter (null)
        // This will trigger the fallback: ObjectManager::getInstance()->get()
        $controller = new Edit(
            $this->contextMock,
            $this->registryMock,
            $this->resultPageFactoryMock,
            null  // This triggers the ObjectManager fallback
        );

        $this->assertInstanceOf(Edit::class, $controller);
    }

    /**
     * Test execute method with valid attribute set ID
     *
     * @return void
     */
    public function testExecuteWithValidAttributeSetId(): void
    {
        $attributeSetId = 18;
        $attributeSetName = 'Test Attribute Set';

        // Mock attribute set
        $attributeSetMock = $this->getMockBuilder(AttributeSetInterface::class)
            ->onlyMethods(['getAttributeSetName'])
            ->addMethods(['getId'])
            ->getMockForAbstractClass();
        $attributeSetMock->expects($this->any())
            ->method('getId')
            ->willReturn($attributeSetId);
        $attributeSetMock->expects($this->any())
            ->method('getAttributeSetName')
            ->willReturn($attributeSetName);

        // Mock request
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('id')
            ->willReturn($attributeSetId);

        // Mock repository
        $this->attributeSetRepositoryMock->expects($this->once())
            ->method('get')
            ->with($attributeSetId)
            ->willReturn($attributeSetMock);

        // Mock registry - both for _setTypeId() and attribute set registration
        $this->registryMock->expects($this->exactly(2))
            ->method('register')
            ->willReturnCallback(function ($key, $value) use ($attributeSetMock) {
                if ($key === 'entityType') {
                    return null;
                } elseif ($key === 'current_attribute_set' && $value === $attributeSetMock) {
                    return null;
                }
            });

        // Mock page result
        $resultPageMock = $this->createMock(ResultPage::class);
        $pageConfigMock = $this->createMock(PageConfig::class);
        $pageTitleMock = $this->createMock(Title::class);

        $resultPageMock->expects($this->once())
            ->method('setActiveMenu')
            ->with('Magento_Catalog::catalog_attributes_sets')
            ->willReturnSelf();
        $resultPageMock->expects($this->any())
            ->method('getConfig')
            ->willReturn($pageConfigMock);
        $pageConfigMock->expects($this->any())
            ->method('getTitle')
            ->willReturn($pageTitleMock);
        $pageTitleMock->expects($this->exactly(2))
            ->method('prepend')
            ->willReturnSelf();
        $resultPageMock->expects($this->exactly(2))
            ->method('addBreadcrumb')
            ->willReturnSelf();

        $this->resultPageFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultPageMock);

        $result = $this->controller->execute();
        $this->assertSame($resultPageMock, $result);
    }

    /**
     * Test execute method with non-existing attribute set ID
     *
     * @return void
     */
    public function testExecuteWithNonExistingAttributeSetId(): void
    {
        $attributeSetId = 999999;

        // Mock request
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('id')
            ->willReturn($attributeSetId);

        // Mock repository to throw exception
        $this->attributeSetRepositoryMock->expects($this->once())
            ->method('get')
            ->with($attributeSetId)
            ->willThrowException(new NoSuchEntityException(__('No such entity!')));

        // Mock message manager
        $this->messageManagerMock->expects($this->once())
            ->method('addErrorMessage')
            ->with(__('Attribute set %1 does not exist.', $attributeSetId));

        // Mock redirect result
        $resultRedirectMock = $this->createMock(ResultRedirect::class);
        $resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('catalog/*/index')
            ->willReturnSelf();

        $this->resultRedirectFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultRedirectMock);

        // Registry should only be called for entityType, not for current_attribute_set
        $this->registryMock->expects($this->once())
            ->method('register')
            ->with('entityType', $this->anything());

        $result = $this->controller->execute();
        $this->assertSame($resultRedirectMock, $result);
    }

    /**
     * Test execute method when attribute set has no ID after loading
     *
     * @return void
     */
    public function testExecuteWithInvalidAttributeSet(): void
    {
        $attributeSetId = 18;

        // Mock attribute set with no ID (invalid)
        $attributeSetMock = $this->getMockBuilder(AttributeSetInterface::class)
            ->addMethods(['getId'])
            ->getMockForAbstractClass();
        $attributeSetMock->expects($this->any())
            ->method('getId')
            ->willReturn(null);

        // Mock request
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('id')
            ->willReturn($attributeSetId);

        // Mock repository
        $this->attributeSetRepositoryMock->expects($this->once())
            ->method('get')
            ->with($attributeSetId)
            ->willReturn($attributeSetMock);

        // Mock redirect result
        $resultRedirectMock = $this->createMock(ResultRedirect::class);
        $resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('catalog/*/index')
            ->willReturnSelf();

        $this->resultRedirectFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultRedirectMock);

        // Registry should only be called for entityType, not for current_attribute_set
        $this->registryMock->expects($this->once())
            ->method('register')
            ->with('entityType', $this->anything());

        $result = $this->controller->execute();
        $this->assertSame($resultRedirectMock, $result);
    }
}
