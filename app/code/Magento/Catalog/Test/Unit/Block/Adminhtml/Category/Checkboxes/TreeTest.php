<?php
declare(strict_types=1);
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */

namespace Magento\Catalog\Test\Unit\Block\Adminhtml\Category\Checkboxes;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Model\Auth\Session;
use Magento\Catalog\Block\Adminhtml\Category\Checkboxes\Tree as CheckboxesTreeBlock;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\ResourceModel\Category\Tree as CategoryTreeResource;
use Magento\Framework\Data\Tree\Node;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Helper\SecureHtmlRenderer;
use Magento\Framework\DB\Helper as DbHelper;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TreeTest extends TestCase
{
    /** @var ObjectManager */
    /** @var EncoderInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $jsonEncoderMock;
    /** @var CategoryFactory|\PHPUnit\Framework\MockObject\MockObject */
    private $categoryFactoryMock;

    protected function setUp(): void
    {
        $this->jsonEncoderMock = $this->createMock(EncoderInterface::class);
        $this->categoryFactoryMock = $this->createMock(CategoryFactory::class);
    }

    /**
     * Provide a stub collection that supports chaining and iteration.
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    private function createCategoryCollectionStub(array $paths)
    {
        // Simple iterable stub with chainable methods
        return new class($paths) implements \IteratorAggregate, \Countable {
            /** @var array */
            private $items;
            /** @var array|null */
            public $lastFilter;
            public function __construct(array $paths)
            {
                $items = [];
                foreach ($paths as $path) {
                    $items[] = new class($path) {
                        /** @var string */
                        private $path;
                        public function __construct(string $path)
                        {
                            $this->path = $path;
                        }
                        public function getPath()
                        {
                            return $this->path;
                        }
                    };
                }
                $this->items = $items;
            }
            public function addAttributeToSelect($arg)
            {
                return $this;
            }
            /**
             * @SuppressWarnings(PHPMD.UnusedLocalVariable)
             * @SuppressWarnings(PHPMD.UnusedFormalParameter)
             */
            public function addAttributeToFilter(...$args)
            {
                $this->lastFilter = $args;
                return $this;
            }
            public function getIterator(): \Traversable
            {
                return new \ArrayIterator($this->items);
            }
            public function count(): int
            {
                return count($this->items);
            }
        };
    }

    private function buildBlockMock(): CheckboxesTreeBlock
    {
        $block = $this->getMockBuilder(CheckboxesTreeBlock::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['_getNodeJson', 'getRoot', 'getRootByIds'])
            ->getMock();

        // Helper to set protected properties across inheritance chain
        $setProp = function ($object, $prop, $value) {
            $class = new \ReflectionClass($object);
            while ($class) {
                if ($class->hasProperty($prop)) {
                    $p = $class->getProperty($prop);
                    $p->setAccessible(true);
                    $p->setValue($object, $value);
                    return;
                }
                $class = $class->getParentClass();
            }
            $this->fail('Property ' . $prop . ' not found in inheritance chain');
        };

        // Inject required collaborators
        $setProp($block, '_jsonEncoder', $this->jsonEncoderMock);
        $setProp($block, '_categoryFactory', $this->categoryFactoryMock);
        return $block;
    }

    public function testGetCategoryIdsReturnsSelectedIds()
    {
        $block = $this->buildBlockMock();
        // Stub factory->create()->getCollection() used by setCategoryIds
        $collectionStub = $this->createCategoryCollectionStub(['1/2/3']);
        $categoryModelMock = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['getCollection'])
            ->getMock();
        $categoryModelMock->method('getCollection')->willReturn($collectionStub);
        $this->categoryFactoryMock->method('create')->willReturn($categoryModelMock);

        $block->setCategoryIds([1, 2, 3]);
        $this->assertSame([1, 2, 3], $block->getCategoryIds());
    }

    public function testSetCategoryIdsPrecomputesExpandedPath()
    {
        $paths = ['1/2/3/4', '1/2/10/20/30'];
        $collectionStub = $this->createCategoryCollectionStub($paths);

        $categoryModelMock = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['getCollection'])
            ->getMock();
        $categoryModelMock->method('getCollection')->willReturn($collectionStub);

        $this->categoryFactoryMock->method('create')->willReturn($categoryModelMock);

        $block = $this->buildBlockMock();

        // Act
        $block->setCategoryIds([4, 30]);

        // Assert expanded path contains all ancestors from both paths
        $reflection = new \ReflectionClass($block);
        $prop = $reflection->getProperty('_expandedPath');
        $prop->setAccessible(true);
        $expanded = $prop->getValue($block);

        foreach (['1','2','3','4','10','20','30'] as $expectedId) {
            $this->assertContains($expectedId, $expanded, 'Expanded path should include ancestor id ' . $expectedId);
        }
    }

    public function testSetCategoryIdsWithEmptyInputProducesEmptyArrayAndSkipsDb()
    {
        $block = $this->buildBlockMock();
        // Ensure factory is not called when ids are empty
        $this->categoryFactoryMock->expects($this->never())->method('create');

        $block->setCategoryIds(null);
        $this->assertSame([], $block->getCategoryIds());

        $block->setCategoryIds('');
        $this->assertSame([], $block->getCategoryIds());
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function testSetCategoryIdsWithScalarCastsToIntArrayAndFiltersCollection()
    {
        $block = $this->buildBlockMock();

        // Custom stub that captures the last filter applied
        $collectionStub = new class() implements \IteratorAggregate, \Countable {
            /** @var array */
            private $items = [];
            /** @var array|null */
            public $lastFilter;
            public function addAttributeToSelect($arg)
            {
                return $this;
            }
            /**
             * @SuppressWarnings(PHPMD.UnusedLocalVariable)
             * @SuppressWarnings(PHPMD.UnusedFormalParameter)
             */
            public function addAttributeToFilter(...$args)
            {
                $this->lastFilter = $args;
                return $this;
            }
            public function getIterator(): \Traversable
            {
                return new \ArrayIterator($this->items);
            }
            public function count(): int
            {
                return count($this->items);
            }
        };
        $categoryModelMock = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['getCollection'])
            ->getMock();
        $categoryModelMock->method('getCollection')->willReturn($collectionStub);
        $this->categoryFactoryMock->method('create')->willReturn($categoryModelMock);

        $block->setCategoryIds('5');

        // Selected ids should be array with integer 5
        $this->assertSame([5], $block->getCategoryIds());
        // Collection should have been filtered with IN ([5])
        $this->assertIsArray($collectionStub->lastFilter);
        $this->assertSame('entity_id', $collectionStub->lastFilter[0]);
        $this->assertSame(['in' => [5]], $collectionStub->lastFilter[1]);
    }

    public function testGetTreeUsesParentNodeWhenProvided()
    {
        $block = $this->buildBlockMock();
        $parent = new Node(['id' => 99, 'children' => []], 'id', new \Magento\Framework\Data\Tree());
        $block->expects($this->once())->method('getRoot')->with($parent)->willReturn($parent);
        $block->expects($this->once())
            ->method('_getNodeJson')
            ->with($parent)
            ->willReturn([
                'children' => [
                    ['id' => 1],
                ],
            ]);
        $tree = $block->getTree($parent);
        $this->assertSame([['id' => 1]], $tree);
    }

    public function testGetTreeReturnsChildrenWhenSelectedIdsPresent()
    {
        $ids = [5];
        $block = $this->buildBlockMock();
        // Stub factory collection for setCategoryIds
        $collectionStub = $this->createCategoryCollectionStub(['1/2/5']);
        $categoryModelMock = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['getCollection'])
            ->getMock();
        $categoryModelMock->method('getCollection')->willReturn($collectionStub);
        $this->categoryFactoryMock->method('create')->willReturn($categoryModelMock);
        $root = new Node(['id' => 1, 'children' => []], 'id', new \Magento\Framework\Data\Tree());
        $block->expects($this->once())->method('getRootByIds')->with($ids)->willReturn($root);
        $block->expects($this->once())->method('_getNodeJson')->with($root)->willReturn(['children' => [['id' => 2]]]);
        $block->setCategoryIds($ids);
        $this->assertSame([['id' => 2]], $block->getTree());
    }

    public function testGetTreeUsesGetRootWhenNoSelectedIds()
    {
        $block = $this->buildBlockMock();
        $root = new Node(['id' => 1, 'children' => []], 'id', new \Magento\Framework\Data\Tree());
        $block->expects($this->once())->method('getRoot')->with(null)->willReturn($root);
        $block->expects($this->once())->method('_getNodeJson')->with($root)->willReturn(['children' => [['id' => 7]]]);

        // Do not call setCategoryIds() so selected IDs remain empty
        $this->assertSame([['id' => 7]], $block->getTree());
    }

    public function testGetTreeJsonUsesLoadByIdsWhenSelectedIdsProvided()
    {
        $ids = [40];
        $block = $this->buildBlockMock();

        // Stub getRootByIds to be called when ids are present, and ensure getRoot is not called
        $rootNode = new Node(['id' => 1, 'children' => []], 'id', new \Magento\Framework\Data\Tree());
        $block->expects($this->once())->method('getRootByIds')->with($ids)->willReturn($rootNode);
        $block->expects($this->never())->method('getRoot');
        $block->expects($this->once())->method('_getNodeJson')->with($rootNode)->willReturn(['children' => []]);
        $this->jsonEncoderMock->method('encode')->with([])->willReturn('[]');

        // Ensure setCategoryIds() can compute expanded paths without touching real DB
        $collectionStub = $this->createCategoryCollectionStub(['1/2/3/40']);
        $categoryModelMock = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['getCollection'])
            ->getMock();
        $categoryModelMock->method('getCollection')->willReturn($collectionStub);
        $this->categoryFactoryMock->method('create')->willReturn($categoryModelMock);

        $block->setCategoryIds($ids);
        $json = $block->getTreeJson();
        $this->assertSame('[]', $json);
    }

    public function testGetTreeJsonUsesGetRootWhenNoSelectedIds()
    {
        $block = $this->buildBlockMock();
        $root = new Node(['id' => 1, 'children' => []], 'id', new \Magento\Framework\Data\Tree());
        $block->expects($this->once())->method('getRoot')->with(null)->willReturn($root);
        $block->expects($this->once())
            ->method('_getNodeJson')
            ->with($root)
            ->willReturn([
                'children' => [
                    ['id' => 3],
                ],
            ]);
        $this->jsonEncoderMock->method('encode')->with([['id' => 3]])->willReturn('[{"id":3}]');
        $this->assertSame('[{"id":3}]', $block->getTreeJson());
    }

    public function testGetTreeJsonUsesParentNodeWhenProvided()
    {
        $block = $this->buildBlockMock();
        $parent = new Node(['id' => 99, 'children' => []], 'id', new \Magento\Framework\Data\Tree());
        $block->expects($this->once())->method('getRoot')->with($parent)->willReturn($parent);
        $block->expects($this->once())->method('_getNodeJson')->with($parent)->willReturn(
            ['children' => [['id' => 11]]]
        );
        $this->jsonEncoderMock->method('encode')
            ->with([
                [
                    'id' => 11,
                ],
            ])
            ->willReturn('[{"id":11}]');

        $json = $block->getTreeJson($parent);
        $this->assertSame('[{"id":11}]', $json);
    }

    public function testGetRootByIdsBuildsTreeAndReturnsRoot()
    {
        // Create a partial mock that will use the real getRootByIds implementation
        $block = $this->getMockBuilder(CheckboxesTreeBlock::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getCategoryCollection'])
            ->getMock();

        // Inject _categoryTree and other props
        $resourceTree = $this->createMock(CategoryTreeResource::class);
        $resourceTree->method('getExistingCategoryIdsBySpecifiedIds')->willReturnCallback(function ($ids) {
            return $ids;
        });
        $resourceTree->method('loadByIds')->willReturn($resourceTree);
        $rootNode = new Node(['id' => 1, 'children' => []], 'id', new \Magento\Framework\Data\Tree());
        $resourceTree->method('getNodeById')->willReturn($rootNode);
        $resourceTree->method('addCollectionData')->willReturn($resourceTree);

        $setProp = function ($object, $prop, $value) {
            $class = new \ReflectionClass($object);
            while ($class) {
                if ($class->hasProperty($prop)) {
                    $p = $class->getProperty($prop);
                    $p->setAccessible(true);
                    $p->setValue($object, $value);
                    return;
                }
                $class = $class->getParentClass();
            }
            $this->fail('Property ' . $prop . ' not found in inheritance chain');
        };
        $setProp($block, '_categoryTree', $resourceTree);
        $setProp($block, '_jsonEncoder', $this->jsonEncoderMock);

        // Stub category collection
        $block->method('getCategoryCollection')->willReturn(
            new class() implements \IteratorAggregate, \Countable {
                /** @var array */
                private $items = [];
                public function getIterator(): \Traversable
                {
                    return new \ArrayIterator($this->items);
                }
                public function count(): int
                {
                    return count($this->items);
                }
            }
        );

        $result = $block->getRootByIds([10]);
        $this->assertInstanceOf(Node::class, $result);
        $this->assertEquals(1, $result->getId());
    }

    public function testGetNodeJsonBuildsExpectedArray()
    {
        // Build a block mock that can call the real _getNodeJson and stubs escapeHtml
        $block = $this->getMockBuilder(CheckboxesTreeBlock::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['escapeHtml'])
            ->getMock();

        $block->method('escapeHtml')->willReturnCallback(function ($v) {
            return $v;
        });

        // Inject required props
        $setProp = function ($object, $prop, $value) {
            $class = new \ReflectionClass($object);
            while ($class) {
                if ($class->hasProperty($prop)) {
                    $p = $class->getProperty($prop);
                    $p->setAccessible(true);
                    $p->setValue($object, $value);
                    return;
                }
                $class = $class->getParentClass();
            }
            $this->fail('Property ' . $prop . ' not found in inheritance chain');
        };
        $setProp($block, '_withProductCount', true);
        // Stub factory for setCategoryIds
        $collectionStub = $this->createCategoryCollectionStub(['1/2/5']);
        $categoryModelMock = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['getCollection'])
            ->getMock();
        $categoryModelMock->method('getCollection')->willReturn($collectionStub);
        $this->categoryFactoryMock->method('create')->willReturn($categoryModelMock);
        // Inject factory into the block
        $setProp($block, '_categoryFactory', $this->categoryFactoryMock);
        $block->setCategoryIds([5]);

        // Node data
        $node = new Node([
            'id' => 5,
            'name' => 'Cat',
            'path' => '1/2/5',
            'is_active' => 1,
            'level' => 2,
            'children' => [],
            'children_count' => 0,
            'product_count' => 0,
        ], 'id', new \Magento\Framework\Data\Tree());

        $ref = new \ReflectionClass($block);
        $method = $ref->getMethod('_getNodeJson');
        $method->setAccessible(true);
        $result = $method->invoke($block, $node, 1);

        $this->assertArrayHasKey('id', $result);
        $this->assertSame(5, $result['id']);
        $this->assertSame('Cat (0)', $result['text']);
        $this->assertTrue($result['checked']);
        $this->assertArrayHasKey('expanded', $result);
        if (array_key_exists('children', $result)) {
            $this->assertIsArray($result['children']);
        }
    }

    public function testGetNodeJsonMarksExpandedWhenLevelLessThanTwo()
    {
        $block = $this->getMockBuilder(CheckboxesTreeBlock::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['escapeHtml'])
            ->getMock();
        $block->method('escapeHtml')->willReturnCallback(function ($v) {
            return $v;
        });

        $node = new Node([
            'id' => 2,
            'name' => 'Root Child',
            'path' => '1/2',
            'is_active' => 1,
            'level' => 1,
            'children_count' => 0,
        ], 'id', new \Magento\Framework\Data\Tree());

        $ref = new \ReflectionClass($block);
        $method = $ref->getMethod('_getNodeJson');
        $method->setAccessible(true);
        $result = $method->invoke($block, $node, 1);

        $this->assertTrue($result['expanded']);
    }

    public function testGetNodeJsonCreatesChildrenArrayWhenNodeHasChildren()
    {
        $block = $this->getMockBuilder(CheckboxesTreeBlock::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['escapeHtml'])
            ->getMock();
        $block->method('escapeHtml')->willReturnCallback(function ($v) {
            return $v;
        });

        $child = new Node([
            'id' => 6,
            'name' => 'Child',
            'path' => '1/2/6',
            'is_active' => 1,
            'level' => 2,
            'children_count' => 0,
        ], 'id', new \Magento\Framework\Data\Tree());

        $parent = new Node([
            'id' => 2,
            'name' => 'Parent',
            'path' => '1/2',
            'is_active' => 1,
            'level' => 1,
            'children' => [],
            'children_count' => 1,
        ], 'id', new \Magento\Framework\Data\Tree());
        $parent->addChild($child);

        $ref = new \ReflectionClass($block);
        $method = $ref->getMethod('_getNodeJson');
        $method->setAccessible(true);
        $result = $method->invoke($block, $parent, 1);

        $this->assertArrayHasKey('children', $result);
        $this->assertNotEmpty($result['children']);
        $this->assertSame(6, $result['children'][0]['id']);
    }

    public function testGetNodeJsonSetsEmptyChildrenWhenCountPositiveButNoChildren()
    {
        $block = $this->getMockBuilder(CheckboxesTreeBlock::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['escapeHtml'])
            ->getMock();
        $block->method('escapeHtml')->willReturnCallback(function ($v) {
            return $v;
        });

        $node = new Node([
            'id' => 8,
            'name' => 'Lazy',
            'path' => '1/8',
            'is_active' => 1,
            'level' => 1,
            // no 'children' provided, but children_count > 0 to simulate lazy-loading marker
            'children_count' => 3,
        ], 'id', new \Magento\Framework\Data\Tree());

        $ref = new \ReflectionClass($block);
        $method = $ref->getMethod('_getNodeJson');
        $method->setAccessible(true);
        $result = $method->invoke($block, $node, 1);

        $this->assertArrayHasKey('children', $result);
        $this->assertSame([], $result['children']);
    }

    public function testPrepareLayoutSetsTemplate()
    {
        $block = $this->getMockBuilder(CheckboxesTreeBlock::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['setTemplate'])
            ->getMock();

        $block->expects($this->once())
            ->method('setTemplate')
            ->with('Magento_Catalog::catalog/category/checkboxes/tree.phtml');

        $ref = new \ReflectionClass($block);
        $method = $ref->getMethod('_prepareLayout');
        $method->setAccessible(true);
        $method->invoke($block);
    }
}
