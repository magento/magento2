<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Block\Adminhtml\Category\Tab;

use Magento\Backend\Helper\Data as BackendHelper;
use Magento\Backend\Model\Session as BackendSession;
use Magento\Catalog\Block\Adminhtml\Category\Tab\Product as CategoryTabProductBlock;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\DB\Select;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Framework\Math\Random;

/**
 * Class for product collection tests
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductBlockPrepareCollectionTest extends TestCase
{
    /**
     * @var BackendHelper|MockObject
     */
    private $backendHelperMock;

    /**
     * @var BackendSession|MockObject
     */
    private $backendSessionMock;

    /**
     * @var (HttpRequest&MockObject)|MockObject
     */
    private $requestMock;

    /**
     * @var ProductCollectionFactory|MockObject
     */
    private $collectionFactoryMock;

    /**
     * @var ProductCollection|MockObject
     */
    private $collectionMock;

    /**
     * @var Select|MockObject
     */
    private $selectMock;

    /**
     * @var Visibility|MockObject
     */
    private $visibilityMock;

    /**
     * @var Status|MockObject
     */
    private $statusMock;

    /**
     * @var MockObject
     */
    private $mathRandomMock;

    protected function setUp(): void
    {
        $this->backendHelperMock = $this->createMock(BackendHelper::class);
        $this->backendSessionMock = $this->createMock(BackendSession::class);

        // Use Http request so getPost() exists
        $this->requestMock = $this->createPartialMock(HttpRequest::class, ['getParam', 'getPost', 'has']);

        $this->collectionFactoryMock = $this->createMock(ProductCollectionFactory::class);
        $this->collectionMock = $this->createMock(ProductCollection::class);
        $this->selectMock = $this->createMock(Select::class);

        $this->visibilityMock = $this->createMock(Visibility::class);
        $this->statusMock = $this->createMock(Status::class);

        // mathRandom for getId() inside Grid::getParam()
        $this->mathRandomMock = $this->createPartialMock(Random::class, ['getUniqueHash']);
        $this->mathRandomMock->method('getUniqueHash')->willReturn('id_test');

        $this->collectionFactoryMock->method('create')->willReturn($this->collectionMock);

        // Fluent + no-op DB calls
        $this->collectionMock->method('addAttributeToSelect')->willReturnSelf();
        $this->collectionMock->method('addStoreFilter')->willReturnSelf();
        $this->collectionMock->method('joinField')->willReturnSelf();
        $this->collectionMock->method('getSelect')->willReturn($this->selectMock);
        $this->collectionMock->method('load')->willReturnSelf();
        $this->selectMock->method('group')->willReturnSelf();

        $this->requestMock->method('has')->willReturn(false);
    }

    private function buildBlock(array $methodsToMock, object $categoryStub): CategoryTabProductBlock
    {
        $block = $this->createPartialMock(CategoryTabProductBlock::class, $methodsToMock);

        // private Product::$productCollectionFactory
        $declProduct = new \ReflectionClass(CategoryTabProductBlock::class);
        $propPcf = $declProduct->getProperty('productCollectionFactory');
        $propPcf->setAccessible(true);
        $propPcf->setValue($block, $this->collectionFactoryMock);

        // Grid protected deps
        $declGrid = new \ReflectionClass(\Magento\Backend\Block\Widget\Grid::class);

        $bhProp = $declGrid->getProperty('_backendHelper');
        $bhProp->setAccessible(true);
        $bhProp->setValue($block, $this->backendHelperMock);

        $bsProp = $declGrid->getProperty('_backendSession');
        $bsProp->setAccessible(true);
        $bsProp->setValue($block, $this->backendSessionMock);

        // AbstractBlock::_request
        $declAbs = new \ReflectionClass(\Magento\Framework\View\Element\AbstractBlock::class);
        $reqProp = $declAbs->getProperty('_request');
        $reqProp->setAccessible(true);
        $reqProp->setValue($block, $this->requestMock);

        // Backend\Block\Template::$mathRandom for getId() calls in Grid::getParam()
        $declTpl = new \ReflectionClass(\Magento\Backend\Block\Template::class);
        $mrProp = $declTpl->getProperty('mathRandom');
        $mrProp->setAccessible(true);
        $mrProp->setValue($block, $this->mathRandomMock);

        // Avoid Grid column lookups
        if (in_array('getColumn', $methodsToMock, true)) {
            $block->method('getColumn')->willReturn(null);
        }
        // Provide category
        if (in_array('getCategory', $methodsToMock, true)) {
            $block->method('getCategory')->willReturn($categoryStub);
        }

        return $block;
    }

    public function testPrepareCollectionWithCategoryIdAndNoStore(): void
    {
        $categoryStub = new class {
            public function getId()
            {
                return 42;
            }
            public function getProductsReadonly()
            {
                return false;
            }
        };

        $this->requestMock->method('getParam')
            ->willReturnMap([
                ['store', 0, 0],
                ['id', 0, 42],
            ]);

        $this->collectionMock->expects($this->once())
            ->method('addAttributeToSelect')
            ->with(
                $this->callback(fn($attrs) =>
                    is_array($attrs) && in_array('name', $attrs, true) && in_array('price', $attrs, true)),
                'left'
            )
            ->willReturnSelf();

        $this->collectionMock->expects($this->once())
            ->method('joinField')
            ->with(
                'position',
                'catalog_category_product',
                'position',
                'product_id=entity_id',
                'category_id=42',
                'left'
            )
            ->willReturnSelf();

        $block = $this->buildBlock(['getCategory', 'getColumn', 'setDefaultFilter'], $categoryStub);
        $block->expects($this->once())->method('setDefaultFilter')->with(['in_category' => 1])->willReturnSelf();

        $this->invokePrepareCollection($block);

        $this->assertSame($this->collectionMock, $block->getCollection());
    }

    public function testPrepareCollectionWithStoreAndReadonly(): void
    {
        $categoryStub = new class {
            public function getId()
            {
                return null;
            }
            public function getProductsReadonly()
            {
                return true;
            }
            public function getProductsPosition()
            {
                return [5 => 10, 7 => 20];
            }
        };

        $this->requestMock->method('getParam')
            ->willReturnMap([
                ['store', 0, 3],
                ['id', 0, 99],
            ]);
        $this->requestMock->method('getPost')->willReturn(null);

        $this->collectionMock->expects($this->once())->method('addStoreFilter')->with(3)->willReturnSelf();

        $this->collectionMock->expects($this->once())
            ->method('joinField')
            ->with(
                'position',
                'catalog_category_product',
                'position',
                'product_id=entity_id',
                'category_id=99',
                'left'
            )
            ->willReturnSelf();

        $this->collectionMock->expects($this->once())
            ->method('addFieldToFilter')
            ->with('entity_id', ['in' => [5, 7]])
            ->willReturnSelf();

        $block = $this->buildBlock(['getCategory', 'getColumn'], $categoryStub);

        $this->invokePrepareCollection($block);

        $this->assertSame($this->collectionMock, $block->getCollection());
    }

    private function invokePrepareCollection(object $block): void
    {
        $m = (new \ReflectionClass($block))->getMethod('_prepareCollection');
        $m->setAccessible(true);
        $m->invoke($block);
    }
}
