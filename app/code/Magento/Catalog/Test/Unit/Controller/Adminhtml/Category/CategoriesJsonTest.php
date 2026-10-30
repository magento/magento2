<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Controller\Adminhtml\Category;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Auth\Session as AuthSession;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Catalog\Block\Adminhtml\Category\Tree;
use Magento\Catalog\Controller\Adminhtml\Category\CategoriesJson;
use Magento\Catalog\Model\Category as CategoryModel;
use Magento\Cms\Model\Wysiwyg\Config as WysiwygConfig;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Controller\Result\Json as ResultJson;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Layout;
use Magento\Framework\View\LayoutFactory;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Unit tests for CategoriesJson controller.
 *
 * @covers \Magento\Catalog\Controller\Adminhtml\Category\CategoriesJson
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.UnusedLocalVariable)
 */
class CategoriesJsonTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var Context|MockObject
     */
    private $contextMock;

    /**
     * @var AuthSession|MockObject
     */
    private $authSessionMock;

    /**
     * @var HttpRequest|MockObject
     */
    private $requestMock;

    /**
     * @var JsonFactory|MockObject
     */
    private $resultJsonFactoryMock;

    /**
     * @var ResultJson|MockObject
     */
    private $resultJsonMock;

    /**
     * @var LayoutFactory|MockObject
     */
    private $layoutFactoryMock;

    /**
     * @var Layout|MockObject
     */
    private $layoutMock;

    /**
     * @var RedirectFactory|MockObject
     */
    private $resultRedirectFactoryMock;

    /**
     * @var Redirect|MockObject
     */
    private $resultRedirectMock;

    /**
     * @return void
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->contextMock = $this->createMock(Context::class);

        // Lightweight stub for AuthSession to track expansion flag and call count without heavy constructor
        $this->authSessionMock = new class extends AuthSession {
            /** @var bool|null */
            public $expandedFlag = null;
            /** @var int */
            public $expandedFlagCallCount = 0;
            public function __construct()
            {
            }
            /**
             * @inheritDoc
             * @return $this
             * @SuppressWarnings(PHPMD.UnusedLocalVariable)
             */
            public function setIsTreeWasExpanded($flag = false)
            {
                $this->expandedFlag = (bool) $flag;
                $this->expandedFlagCallCount++;
                return $this;
            }
        };

        $this->resultJsonFactoryMock = $this->createMock(JsonFactory::class);
        $this->resultJsonMock         = $this->createMock(ResultJson::class);
        $this->layoutFactoryMock      = $this->createMock(LayoutFactory::class);
        $this->layoutMock             = $this->createMock(Layout::class);
        $this->requestMock            = $this->createMock(HttpRequest::class);

        $this->resultRedirectFactoryMock = $this->getMockBuilder(RedirectFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultRedirectMock = $this->getMockBuilder(Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->contextMock->method('getResultRedirectFactory')
            ->willReturn($this->resultRedirectFactoryMock);
        $this->contextMock->method('getRequest')
            ->willReturn($this->requestMock);

        $storeManagerMock   = $this->createMock(StoreManagerInterface::class);
        $registryMock       = $this->createMock(Registry::class);
        $wysiwygConfigMock  = $this->createMock(WysiwygConfig::class);

        $this->objectManager->prepareObjectManager([
            [StoreManagerInterface::class, $storeManagerMock],
            [Registry::class, $registryMock],
            [WysiwygConfig::class, $wysiwygConfigMock],
            [AuthSession::class, $this->authSessionMock],
        ]);
    }

    /**
     * Data provider: expand_all values and missing ID values that should yield JSON error.
     *
     * @return array
     */
    public static function missingIdProvider(): array
    {
        return [
            'expand truthy, id null' => [1, true, null],
            'expand falsy, id zero int' => [0, false, 0],
            'expand null, id null' => [null, false, null],
            'expand truthy string, id "0" string' => ['1', true, '0'],
            'expand falsy empty string, id empty string' => ['', false, ''],
            'expand bool true, id 0' => [true, true, 0],
            'expand bool false, id null' => [false, false, null],
        ];
    }

    /**
     * Ensure execute() sets expanded flag accordingly and returns JSON error when id is missing.
     *
     * @covers \Magento\Catalog\Controller\Adminhtml\Category\CategoriesJson::execute
     * @return void
     */
    #[DataProvider('missingIdProvider')]
    public function testExecuteWithMissingIdReturnsJsonError($expandAllValue, bool $expectedExpanded, $postId): void
    {
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('expand_all')
            ->willReturn($expandAllValue);
        $this->requestMock->expects($this->once())
            ->method('getPost')
            ->with('id')
            ->willReturn($postId);

        $this->resultJsonFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->resultJsonMock);
        $this->resultJsonMock->expects($this->once())
            ->method('setJsonData')
            ->with(json_encode(['error' => 'Category ID is required']))
            ->willReturnSelf();

        $controller = $this->objectManager->getObject(
            CategoriesJson::class,
            [
                'context'           => $this->contextMock,
                'resultJsonFactory' => $this->resultJsonFactoryMock,
                'layoutFactory'     => $this->layoutFactoryMock,
                'authSession'       => $this->authSessionMock,
            ]
        );

        $result = $controller->execute();
        $this->assertSame($this->resultJsonMock, $result);
        $this->assertSame($expectedExpanded, $this->authSessionMock->expandedFlag);
        $this->assertSame(1, $this->authSessionMock->expandedFlagCallCount);
    }

    /**
     * Ensure redirect is returned when _initCategory() yields null.
     *
     * @covers \Magento\Catalog\Controller\Adminhtml\Category\CategoriesJson::execute
     * @return void
     */
    public function testExecuteWithValidIdAndNullInitCategoryReturnsRedirect(): void
    {
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('expand_all')
            ->willReturn(null);
        $this->requestMock->expects($this->once())
            ->method('getPost')
            ->with('id')
            ->willReturn(12);
        $this->requestMock->expects($this->once())
            ->method('setParam')
            ->with('id', 12);

        $this->resultRedirectFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->resultRedirectMock);

        $this->resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('catalog/*/', ['_current' => true, 'id' => null])
            ->willReturn($this->resultRedirectMock);

        // Use controller test double that returns null from _initCategory()
        $controller = new class (
            $this->contextMock,
            $this->resultJsonFactoryMock,
            $this->layoutFactoryMock,
            $this->authSessionMock
        ) extends CategoriesJson {
            /** @var CategoryModel|null */
            public $initCategoryResult;
            protected function _initCategory($getRootInstead = false)
            {
                return $this->initCategoryResult;
            }
        };
        $controller->initCategoryResult = null;

        $result = $controller->execute();
        $this->assertSame($this->resultRedirectMock, $result);
        $this->assertSame(false, $this->authSessionMock->expandedFlag);
        $this->assertSame(1, $this->authSessionMock->expandedFlagCallCount);
    }

    /**
     * Ensure JSON is returned with tree block payload when category is initialized.
     *
     * @covers \Magento\Catalog\Controller\Adminhtml\Category\CategoriesJson::execute
     * @return void
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testExecuteWithValidIdReturnsTreeJson(): void
    {
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('expand_all')
            ->willReturn(0);
        $this->requestMock->expects($this->once())
            ->method('getPost')
            ->with('id')
            ->willReturn(34);
        $this->requestMock->expects($this->once())
            ->method('setParam')
            ->with('id', 34);

        $categoryMock = $this->createMock(CategoryModel::class);

        $treeBlockMock = $this->getMockBuilder(Tree::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->layoutFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->layoutMock);
        $this->layoutMock->expects($this->once())
            ->method('createBlock')
            ->with(Tree::class)
            ->willReturn($treeBlockMock);

        $treeBlockMock->expects($this->once())
            ->method('getTreeJson')
            ->with($categoryMock)
            ->willReturn('{"tree":"json"}');

        $this->resultJsonFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->resultJsonMock);
        $this->resultJsonMock->expects($this->once())
            ->method('setJsonData')
            ->with('{"tree":"json"}')
            ->willReturnSelf();

        $controller = new class (
            $this->contextMock,
            $this->resultJsonFactoryMock,
            $this->layoutFactoryMock,
            $this->authSessionMock
        ) extends CategoriesJson {
            /** @var CategoryModel|null */
            public $initCategoryResult;
            protected function _initCategory($getRootInstead = false)
            {
                return $this->initCategoryResult;
            }
        };
        $controller->initCategoryResult = $categoryMock;

        $result = $controller->execute();
        $this->assertSame($this->resultJsonMock, $result);
        $this->assertSame(false, $this->authSessionMock->expandedFlag);
        $this->assertSame(1, $this->authSessionMock->expandedFlagCallCount);
    }
}
