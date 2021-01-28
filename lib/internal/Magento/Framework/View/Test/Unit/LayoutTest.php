<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Test\Unit;

use Magento\Framework\App\State;
use Magento\Framework\Cache\FrontendInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\Design\Theme\ResolverInterface;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Layout;
use Magento\Framework\View\Layout\Data\Structure as LayoutStructure;
use Magento\Framework\View\Layout\Element;
use Magento\Framework\View\Layout\Generator\Block;
use Magento\Framework\View\Layout\Generator\Container;
use Magento\Framework\View\Layout\Generator\Context;
use Magento\Framework\View\Layout\Generator\ContextFactory;
use Magento\Framework\View\Layout\GeneratorPool;
use Magento\Framework\View\Layout\ProcessorFactory;
use Magento\Framework\View\Layout\Reader\ContextFactory as LayoutReaderContextFactory;
use Magento\Framework\View\Layout\ReaderPool;
use Magento\Framework\View\Layout\ScheduledStructure;
use Magento\Framework\View\Model\Layout\Merge;
use Magento\Framework\View\Page\Config\Structure;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class LayoutTest extends TestCase
{
    /**
     * @var Layout
     */
    private $model;

    /**
     * @var PHPUnit\Framework\MockObject\MockObject
     */
    private $structureMock;

    /**
     * @var ProcessorFactory|PHPUnit\Framework\MockObject\MockObject
     */
    private $processorFactoryMock;

    /**
     * @var ResolverInterface|PHPUnit\Framework\MockObject\MockObject
     */
    private $themeResolverMock;

    /**
     * @var Merge|PHPUnit\Framework\MockObject\MockObject
     */
    private $processorMock;

    /**
     * @var EventManager|PHPUnit\Framework\MockObject\MockObject
     */
    private $eventManagerMock;

    /**
     * @var Block|PHPUnit\Framework\MockObject\MockObject
     */
    private $generatorBlockMock;

    /**
     * @var Container|PHPUnit\Framework\MockObject\MockObject
     */
    private $generatorContainerMock;

    /**
     * @var FrontendInterface|PHPUnit\Framework\MockObject\MockObject
     */
    private $cacheMock;

    /**
     * @var ReaderPool|PHPUnit\Framework\MockObject\MockObject
     */
    private $readerPoolMock;

    /**
     * @var GeneratorPool|PHPUnit\Framework\MockObject\MockObject
     */
    private $generatorPoolMock;

    /**
     * @var ManagerInterface|PHPUnit\Framework\MockObject\MockObject
     */
    private $messageManagerMock;

    /**
     * @var LayoutReaderContextFactory|PHPUnit\Framework\MockObject\MockObject
     */
    private $readerContextFactoryMock;

    /**
     * @var \Magento\Framework\View\Layout\Reader\Context|PHPUnit\Framework\MockObject\MockObject
     */
    private $readerContextMock;

    /**
     * @var Structure|PHPUnit\Framework\MockObject\MockObject
     */
    private $pageConfigStructure;

    /**
     * @var ScheduledStructure|PHPUnit\Framework\MockObject\MockObject
     */
    private $layoutScheduledSructure;

    /**
     * @var ContextFactory|PHPUnit\Framework\MockObject\MockObject
     */
    private $generatorContextFactoryMock;

    /**
     * @var State|PHPUnit\Framework\MockObject\MockObject
     */
    private $appStateMock;

    /**
     * @var LoggerInterface|PHPUnit\Framework\MockObject\MockObject
     */
    private $loggerMock;

    /**
     * @var SerializerInterface|PHPUnit\Framework\MockObject\MockObject
     */
    private $serializer;

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp(): void
    {
        $this->structureMock = $this->getMockBuilder(LayoutStructure::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->processorFactoryMock = $this->createPartialMock(ProcessorFactory::class, ['create']);
        $this->themeResolverMock = $this->getMockForAbstractClass(ResolverInterface::class);
        $this->processorMock = $this->createMock(Merge::class);
        $this->eventManagerMock = $this->getMockForAbstractClass(EventManager::class);
        $this->generatorBlockMock = $this->getMockBuilder(Block::class)
            ->disableOriginalConstructor()->getMock();
        $this->generatorContainerMock = $this->getMockBuilder(Container::class)
            ->disableOriginalConstructor()->getMock();
        $this->cacheMock = $this->getMockForAbstractClass(FrontendInterface::class);
        $this->readerPoolMock = $this->getMockBuilder(ReaderPool::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->messageManagerMock = $this->getMockForAbstractClass(ManagerInterface::class);
        $this->generatorPoolMock = $this->getMockBuilder(GeneratorPool::class)
            ->disableOriginalConstructor()->getMock();
        $this->generatorPoolMock->expects($this->any())
            ->method('getGenerator')
            ->willReturnMap(
                [
                    [Block::TYPE, $this->generatorBlockMock],
                    [Container::TYPE, $this->generatorContainerMock],
                ]
            );
        $this->readerContextFactoryMock = $this->getMockBuilder(LayoutReaderContextFactory::class)
            ->disableOriginalConstructor()->getMock();
        $this->pageConfigStructure = $this->getMockBuilder(Structure::class)
            ->setMethods(['__toArray', 'populateWithArray'])
            ->getMock();
        $this->layoutScheduledSructure = $this->getMockBuilder(ScheduledStructure::class)
            ->setMethods(['__toArray', 'populateWithArray'])
            ->getMock();
        $this->readerContextMock = $this->getMockBuilder(\Magento\Framework\View\Layout\Reader\Context::class)
            ->setMethods(['getPageConfigStructure', 'getScheduledStructure'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->readerContextMock->expects($this->any())->method('getPageConfigStructure')
            ->willReturn($this->pageConfigStructure);
        $this->readerContextMock->expects($this->any())->method('getScheduledStructure')
            ->willReturn($this->layoutScheduledSructure);
        $this->generatorContextFactoryMock = $this->getMockBuilder(ContextFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->appStateMock = $this->getMockBuilder(State::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);
        $this->serializer = $this->getMockForAbstractClass(SerializerInterface::class);
        $this->serializer->expects($this->any())->method('serialize')
            ->willReturnCallback(
                function ($value) {
                    return json_encode($value);
                }
            );
        $this->serializer->expects($this->any())->method('unserialize')
            ->willReturnCallback(
                function ($value) {
                    return json_decode($value, true);
                }
            );

        $this->model = new Layout(
            $this->processorFactoryMock,
            $this->eventManagerMock,
            $this->structureMock,
            $this->messageManagerMock,
            $this->themeResolverMock,
            $this->readerPoolMock,
            $this->generatorPoolMock,
            $this->cacheMock,
            $this->readerContextFactoryMock,
            $this->generatorContextFactoryMock,
            $this->appStateMock,
            $this->loggerMock,
            true,
            $this->serializer
        );
    }

    public function testCreateBlockSuccess()
    {
        $blockMock = $this->getMockBuilder(AbstractBlock::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->structureMock->expects($this->once())
            ->method('createStructuralElement')
            ->with(
                'blockname',
                Element::TYPE_BLOCK,
                AbstractBlock::class
            )->willReturn('blockname');
        $this->generatorBlockMock->expects($this->once())->method('createBlock')->willReturn($blockMock);

        $this->model->createBlock(AbstractBlock::class, 'blockname', []);
        $this->assertInstanceOf(
            AbstractBlock::class,
            $this->model->getBlock('blockname')
        );
        $this->assertFalse($this->model->getBlock('not_exist'));
    }

    public function testGetUpdate()
    {
        $themeMock = $this->getMockForAbstractClass(ThemeInterface::class);

        $this->themeResolverMock->expects($this->once())
            ->method('get')
            ->willReturn($themeMock);

        $this->processorFactoryMock->expects($this->once())
            ->method('create')
            ->with(['theme' => $themeMock])
            ->willReturn($this->processorMock);

        $this->assertEquals($this->processorMock, $this->model->getUpdate());
        $this->assertEquals($this->processorMock, $this->model->getUpdate());
    }

    public function testGenerateXml()
    {
        $themeMock = $this->getMockForAbstractClass(ThemeInterface::class);

        $this->themeResolverMock->expects($this->once())
            ->method('get')
            ->willReturn($themeMock);

        $this->processorFactoryMock->expects($this->once())
            ->method('create')
            ->with(['theme' => $themeMock])
            ->willReturn($this->processorMock);

        $xmlString = '<?xml version="1.0"?><layout xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">'
            . '<some_update>123</some_update></layout>';
        $xml = simplexml_load_string($xmlString, Element::class);
        $this->processorMock->expects($this->once())
            ->method('asSimplexml')
            ->willReturn($xml);

        $this->structureMock->expects($this->once())
            ->method('importElements')
            ->with($this->equalTo([]))
            ->willReturnSelf();
        $this->assertSame($this->model, $this->model->generateXml());
        $this->assertSame('<some_update>123</some_update>', $this->model->getNode('some_update')->asXML());
    }

    public function testGetChildBlock()
    {
        $customBlockName = 'custom_block';
        $customBlockParentName = 'custom_block_parent';
        $customBlockAlias = 'custom_block_alias';

        $blockMock = $this->getMockBuilder(Template::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->structureMock->expects($this->once())
            ->method('getChildId')
            ->with($customBlockParentName, $customBlockAlias)
            ->willReturn($customBlockName);

        $this->structureMock->expects($this->once())
            ->method('hasElement')
            ->with($customBlockName)
            ->willReturn(true);

        $this->structureMock->expects($this->once())
            ->method('getAttribute')
            ->with($customBlockName, 'type')
            ->willReturn(Element::TYPE_BLOCK);

        $this->model->setBlock($customBlockName, $blockMock);
        $this->assertInstanceOf(
            AbstractBlock::class,
            $this->model->getChildBlock($customBlockParentName, $customBlockAlias)
        );
    }

    public function testGetChildNonExistBlock()
    {
        $this->structureMock->expects($this->once())
            ->method('getChildId')
            ->with('non_exist_parent', 'non_exist_alias')
            ->willReturn(false);
        $this->assertFalse($this->model->getChildBlock('non_exist_parent', 'non_exist_alias'));
    }

    public function testSetChild()
    {
        $elementName = 'child';
        $parentName = 'parent';
        $alias = 'some_alias';
        $this->structureMock->expects($this->once())
            ->method('setAsChild')
            ->with($this->equalTo($elementName), $this->equalTo($parentName), $this->equalTo($alias))
            ->willReturnSelf();
        $this->assertSame($this->model, $this->model->setChild($parentName, $elementName, $alias));
    }

    public function testUnsetChild()
    {
        $parentName = 'parent';
        $alias = 'some_alias';
        $this->structureMock->expects($this->once())
            ->method('unsetChild')
            ->with($this->equalTo($parentName), $this->equalTo($alias))
            ->willReturnSelf();
        $this->assertSame($this->model, $this->model->unsetChild($parentName, $alias));
    }

    public function testGetChildNames()
    {
        $parentName = 'parent';
        $childrenArray = ['key1' => 'value1', 'key2' => 'value2'];
        $this->structureMock->expects($this->once())
            ->method('getChildren')
            ->with($this->equalTo($parentName))
            ->willReturn($childrenArray);
        $this->assertSame(['key1', 'key2'], $this->model->getChildNames($parentName));
    }

    public function testGetChildBlocks()
    {
        $parentName = 'parent';
        $childrenArray = ['block_name' => 'value1'];
        $this->structureMock->expects($this->once())
            ->method('getChildren')
            ->with($this->equalTo($parentName))
            ->willReturn($childrenArray);

        $blockMock = $this->getMockBuilder(AbstractBlock::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->structureMock->expects($this->once())
            ->method('createStructuralElement')
            ->with(
                'block_name',
                Element::TYPE_BLOCK,
                AbstractBlock::class
            )->willReturn('block_name');
        $this->generatorBlockMock->expects($this->once())->method('createBlock')->willReturn($blockMock);

        $this->assertSame(
            $blockMock,
            $this->model->createBlock(AbstractBlock::class, 'block_name', [])
        );
        $this->assertSame(['value1' => $blockMock], $this->model->getChildBlocks($parentName));
    }

    public function testGetChildName()
    {
        $parentName = 'parent';
        $alias = 'some_alias';
        $this->structureMock->expects($this->once())
            ->method('getChildId')
            ->with($this->equalTo($parentName), $this->equalTo($alias))
            ->willReturn('1');
        $this->assertSame('1', $this->model->getChildName($parentName, $alias));
    }

    public function testAddToParentGroup()
    {
        $blockName = 'block_name';
        $parentGroup = 'parent_group';
        $this->structureMock->expects($this->once())
            ->method('addToParentGroup')
            ->with($this->equalTo($blockName), $this->equalTo($parentGroup))
            ->willReturnSelf();
        $this->assertSame($this->structureMock, $this->model->addToParentGroup($blockName, $parentGroup));
    }

    public function testGetGroupChildNames()
    {
        $blockName = 'block_name';
        $groupName = 'group_name';
        $this->structureMock->expects($this->once())
            ->method('getGroupChildNames')
            ->with($this->equalTo($blockName), $this->equalTo($groupName))
            ->willReturnSelf();
        $this->assertSame($this->structureMock, $this->model->getGroupChildNames($blockName, $groupName));
    }

    public function testHasElement()
    {
        $elementName = 'name';
        $this->structureMock->expects($this->once())
            ->method('hasElement')
            ->with($this->equalTo($elementName))
            ->willReturn(true);
        $this->assertTrue($this->model->hasElement($elementName));
    }

    public function testGetElementProperty()
    {
        $elementName = 'name';
        $elementAttr = 'attribute';
        $result = 'result';
        $this->structureMock->expects($this->once())
            ->method('getAttribute')
            ->with($this->equalTo($elementName), $this->equalTo($elementAttr))
            ->willReturn($result);
        $this->assertSame($result, $this->model->getElementProperty($elementName, $elementAttr));
    }

    /**
     * @param bool $hasElement
     * @param string $attribute
     * @param bool $result
     * @dataProvider isContainerDataProvider
     */
    public function testIsContainer($hasElement, $attribute, $result)
    {
        $elementName = 'element_name';
        $this->structureMock->expects($this->once())
            ->method('hasElement')
            ->with($this->equalTo($elementName))
            ->willReturn($hasElement);
        if ($hasElement) {
            $this->structureMock->expects($this->once())
                ->method('getAttribute')
                ->with($this->equalTo($elementName), $this->equalTo('type'))
                ->willReturn($attribute);
        }
        $this->assertSame($result, $this->model->isContainer($elementName));
    }

    /**
     * @return array
     */
    public function isContainerDataProvider()
    {
        return [
            [false, '', false],
            [true, 'container', true],
            [true, 'block', false],
            [true, 'something', false],
        ];
    }

    /**
     * @param bool $parentName
     * @param array $containerConfig
     * @param bool $result
     * @dataProvider isManipulationAllowedDataProvider
     */
    public function testIsManipulationAllowed($parentName, $containerConfig, $result)
    {
        $elementName = 'element_name';
        $this->structureMock->expects($this->once())
            ->method('getParentId')
            ->with($this->equalTo($elementName))
            ->willReturn($parentName);
        if ($parentName) {
            $this->structureMock->expects($this->once())
                ->method('hasElement')
                ->with($this->equalTo($parentName))
                ->willReturn($containerConfig['has_element']);
            if ($containerConfig['has_element']) {
                $this->structureMock->expects($this->once())
                    ->method('getAttribute')
                    ->with($this->equalTo($parentName), $this->equalTo('type'))
                    ->willReturn($containerConfig['attribute']);
            }
        }

        $this->assertSame($result, $this->model->isManipulationAllowed($elementName));
    }

    /**
     * @return array
     */
    public function isManipulationAllowedDataProvider()
    {
        return [
            ['parent', ['has_element' => true, 'attribute' => 'container'], true],
            ['parent', ['has_element' => true, 'attribute' => 'block'], false],
            [false, [], false],
        ];
    }

    /**
     * @covers \Magento\Framework\View\Layout::setBlock
     * @covers \Magento\Framework\View\Layout::getAllBlocks
     * @covers \Magento\Framework\View\Layout::unsetElement
     */
    public function testSetGetBlock()
    {
        $blockName = 'some_name';
        $blockMock = $this->getMockBuilder(AbstractBlock::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->assertSame($this->model, $this->model->setBlock($blockName, $blockMock));
        $this->assertSame([$blockName => $blockMock], $this->model->getAllBlocks());
        $this->structureMock->expects($this->once())
            ->method('unsetElement')
            ->with($this->equalTo($blockName))
            ->willReturnSelf();
        $this->assertSame($this->model, $this->model->unsetElement($blockName));
        $this->assertSame([], $this->model->getAllBlocks());
    }

    public function testRenameElement()
    {
        $oldName = 'old_name';
        $newName = 'new_name';
        $blockMock = $this->getMockBuilder(AbstractBlock::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->structureMock->expects($this->once())
            ->method('renameElement')
            ->with($this->equalTo($oldName), $this->equalTo($newName))
            ->willReturnSelf();
        $this->assertSame($this->model, $this->model->setBlock($oldName, $blockMock));
        $this->assertSame($this->model, $this->model->renameElement($oldName, $newName));
        $this->assertSame([$newName => $blockMock], $this->model->getAllBlocks());
    }

    public function testGetParentName()
    {
        $childName = 'child_name';
        $parentId = 'parent_id';
        $this->structureMock->expects($this->once())
            ->method('getParentId')
            ->with($this->equalTo($childName))
            ->willReturn($parentId);
        $this->assertSame($parentId, $this->model->getParentName($childName));
    }

    public function testGetElementAlias()
    {
        $name = 'child_name';
        $parentId = 'parent_id';
        $alias = 'alias';
        $this->structureMock->expects($this->once())
            ->method('getParentId')
            ->with($this->equalTo($name))
            ->willReturn($parentId);
        $this->structureMock->expects($this->once())
            ->method('getChildAlias')
            ->with($this->equalTo($parentId), $this->equalTo($name))
            ->willReturn($alias);
        $this->assertSame($alias, $this->model->getElementAlias($name));
    }

    public function testAddRemoveOutputElement()
    {
        $this->assertSame($this->model, $this->model->addOutputElement('name'));
        $this->assertSame($this->model, $this->model->removeOutputElement('name'));
    }

    public function testIsPrivate()
    {
        $this->assertFalse($this->model->isPrivate());
        $this->assertSame($this->model, $this->model->setIsPrivate(true));
        $this->assertTrue($this->model->isPrivate());
    }

    /**
     * @param array $type
     * @param array $blockInstance
     * @dataProvider getBlockSingletonDataProvider
     */
    public function testGetBlockSingleton($type, $blockInstance, $isAbstract)
    {
        $blockMock = $this->createMock($blockInstance);
        $this->generatorBlockMock->expects($this->once())->method('createBlock')->willReturn($blockMock);

        if ($isAbstract) {
            $blockMock->expects($this->any())
                ->method('setLayout')
                ->with($this->equalTo($this->model))
                ->willReturnSelf();
        }
        $this->assertInstanceOf($blockInstance, $this->model->getBlockSingleton($type));
        // singleton test
        $this->assertInstanceOf($blockInstance, $this->model->getBlockSingleton($type));
    }

    /**
     * @return array
     */
    public function getBlockSingletonDataProvider()
    {
        return [
            [
                'some_type', Template::class,
                true,
            ],
        ];
    }

    /**
     * @param array $rendererData
     * @param array $getData
     * @param bool $result
     * @dataProvider getRendererOptionsDataProvider
     */
    public function testAddGetRendererOptions($rendererData, $getData, $result)
    {
        $this->assertSame(
            $this->model,
            $this->model->addAdjustableRenderer(
                $rendererData['namespace'],
                $rendererData['static_type'],
                $rendererData['dynamic_type'],
                $rendererData['type'],
                $rendererData['template'],
                $rendererData['data']
            )
        );
        $this->assertSame(
            $result,
            $this->model->getRendererOptions($getData['namespace'], $getData['static_type'], $getData['dynamic_type'])
        );
    }

    /**
     * @return array
     */
    public function getRendererOptionsDataProvider()
    {
        $rendererData = [
            'namespace' => 'namespace_value',
            'static_type' => 'static_type_value',
            'dynamic_type' => 'dynamic_type_value',
            'type' => 'type_value',
            'template' => 'template.phtml',
            'data' => ['some' => 'data'],
        ];
        return [
            'wrong namespace' => [
                $rendererData,
                [
                    'namespace' => 'wrong namespace',
                    'static_type' => 'static_type_value',
                    'dynamic_type' => 'dynamic_type_value',
                ],
                null,
            ],
            'wrong static type' => [
                $rendererData,
                [
                    'namespace' => 'namespace_value',
                    'static_type' => 'wrong static type',
                    'dynamic_type' => 'dynamic_type_value',
                ],
                null,
            ],
            'wrong dynamic type' => [
                $rendererData,
                [
                    'namespace' => 'namespace_value',
                    'static_type' => 'static_type_value',
                    'dynamic_type' => 'wrong dynamic type',
                ],
                null,
            ],
            'set and get test' => [
                $rendererData,
                [
                    'namespace' => 'namespace_value',
                    'static_type' => 'static_type_value',
                    'dynamic_type' => 'dynamic_type_value',
                ],
                [
                    'type' => 'type_value',
                    'template' => 'template.phtml',
                    'data' => ['some' => 'data'],
                ],
            ],
        ];
    }

    /**
     * @param string $xmlString
     * @param string $blockName
     * @param bool $hasElement
     * @param bool $cacheable
     * @return void
     * @dataProvider isCacheableDataProvider
     */
    public function testIsCacheable(string $xmlString, string $blockName, bool $hasElement, bool $cacheable): void
    {
        $this->structureMock->method('hasElement')->with($blockName)->willReturn($hasElement);

        $this->assertTrue($this->model->loadString($xmlString));
        $this->assertSame($cacheable, $this->model->isCacheable());
    }

    /**
     * @return array
     */
    public function isCacheableDataProvider(): array
    {
        return [
            'blockWithoutName' => [
                'xml' => '<?xml version="1.0"?><layout xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">'
                    . '<block></block></layout>',
                'blockName' => '',
                'hasElement' => true,
                'cacheable' => true,
            ],
            'notCacheableBlockWithoutName' => [
                'xml' => '<?xml version="1.0"?><layout xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">'
                    . '<block cacheable="false"></block></layout>',
                'blockName' => '',
                'hasElement' => true,
                'cacheable' => true
            ],
            'notCacheableBlockWithMissingBlockReference' => [
                'xml' => '<?xml version="1.0"?><layout xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">'
                    . '<referenceBlock name="not_existing_block">'
                    . '<block name="non_cacheable_block" cacheable="false"></block>'
                    . '</referenceBlock></layout>',
                'blockName' => 'non_cacheable_block',
                'hasElement' => false,
                'cacheable' => true
            ],
            'notCacheableBlockWithMissingContainerReference' => [
                'xml' => '<?xml version="1.0"?><layout xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">'
                    . '<referenceContainer name="not_existing_container">'
                    . '<block name="non_cacheable_block" cacheable="false"></block>'
                    . '</referenceContainer></layout>',
                'blockName' => 'non_cacheable_block',
                'hasElement' => false,
                'cacheable' => true
            ],
            'notCacheableBlockWithExistingBlockReference' => [
                'xml' => '<?xml version="1.0"?><layout xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">'
                    . '<referenceBlock name="existing_block">'
                    . '<block name="non_cacheable_block" cacheable="false"></block>'
                    . '</referenceBlock></layout>',
                'blockName' => 'non_cacheable_block',
                'hasElement' => true,
                'cacheable' => false
            ],
            'notCacheableBlockWithExistingContainerReference' => [
                'xml' => '<?xml version="1.0"?><layout xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">'
                    . '<referenceContainer name="existing_container">'
                    . '<block name="non_cacheable_block" cacheable="false"></block>'
                    . '</referenceContainer></layout>',
                'blockName' => 'non_cacheable_block',
                'hasElement' => true,
                'cacheable' => false
            ],
        ];
    }

    public function testGenerateElementsWithoutCache()
    {
        $this->readerContextFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->readerContextMock);
        $layoutCacheId = 'layout_cache_id';
        $handles = ['default', 'another'];
        /** @var Element $xml */
        $xml = simplexml_load_string('<layout/>', Element::class);
        $this->model->setXml($xml);

        $themeMock = $this->getMockForAbstractClass(ThemeInterface::class);
        $this->themeResolverMock->expects($this->once())
            ->method('get')
            ->willReturn($themeMock);
        $this->processorFactoryMock->expects($this->once())
            ->method('create')
            ->with(['theme' => $themeMock])
            ->willReturn($this->processorMock);

        $this->processorMock->expects($this->once())
            ->method('getCacheId')
            ->willReturn($layoutCacheId);
        $this->processorMock->expects($this->once())
            ->method('getHandles')
            ->willReturn($handles);

        $this->cacheMock->expects($this->once())
            ->method('load')
            ->with('structure_' . $layoutCacheId)
            ->willReturn(false);

        $this->readerPoolMock->expects($this->once())
            ->method('interpret')
            ->with($this->readerContextMock, $xml)
            ->willReturnSelf();

        $pageConfigStructureData = [
            'field_1' => 123,
            'field_2' => 'text',
            'field_3' => [
                'field_3_1' => '1244',
                'field_3_2' => null,
                'field_3_3' => false,
            ]
        ];
        $this->pageConfigStructure->expects($this->any())->method('__toArray')
            ->willReturn($pageConfigStructureData);

        $layoutScheduledStructureData = [
            'field_1' => 1283,
            'field_2' => 'text_qwertyuiop[]asdfghjkl;'
        ];
        $this->layoutScheduledSructure->expects($this->any())->method('__toArray')
            ->willReturn($layoutScheduledStructureData);
        $data = [
            'pageConfigStructure' => $pageConfigStructureData,
            'scheduledStructure' => $layoutScheduledStructureData
        ];

        $this->cacheMock->expects($this->once())
            ->method('save')
            ->with(json_encode($data), 'structure_' . $layoutCacheId, $handles)
            ->willReturn(true);

        $generatorContextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->generatorContextFactoryMock->expects($this->once())
            ->method('create')
            ->with(['structure' => $this->structureMock, 'layout' => $this->model])
            ->willReturn($generatorContextMock);

        $this->generatorPoolMock->expects($this->once())
            ->method('process')
            ->with($this->readerContextMock, $generatorContextMock)
            ->willReturn(true);

        $elements = [
            'name_1' => ['type' => '', 'parent' => null],
            'name_2' => ['type' => Element::TYPE_CONTAINER, 'parent' => null],
            'name_3' => ['type' => '', 'parent' => 'parent'],
            'name_4' => ['type' => Element::TYPE_CONTAINER, 'parent' => 'parent'],
        ];

        $this->structureMock->expects($this->once())
            ->method('exportElements')
            ->willReturn($elements);

        $this->model->generateElements();
    }

    public function testGenerateElementsWithCache()
    {
        $layoutCacheId = 'layout_cache_id';
        /** @var Element $xml */
        $xml = simplexml_load_string('<layout/>', Element::class);
        $this->model->setXml($xml);

        $this->readerContextFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->readerContextMock);
        $themeMock = $this->getMockForAbstractClass(ThemeInterface::class);
        $this->themeResolverMock->expects($this->once())
            ->method('get')
            ->willReturn($themeMock);
        $this->processorFactoryMock->expects($this->once())
            ->method('create')
            ->with(['theme' => $themeMock])
            ->willReturn($this->processorMock);

        $this->processorMock->expects($this->once())
            ->method('getCacheId')
            ->willReturn($layoutCacheId);

        $pageConfigStructureData = [
            'field_1' => 123,
            'field_2' => 'text',
            'field_3' => [
                'field_3_1' => '1244',
                'field_3_2' => null,
                'field_3_3' => false,
            ]
        ];
        $this->pageConfigStructure->expects($this->once())->method('populateWithArray')
            ->with($pageConfigStructureData);

        $layoutScheduledStructureData = [
            'field_1' => 1283,
            'field_2' => 'text_qwertyuiop[]asdfghjkl;'
        ];
        $this->layoutScheduledSructure->expects($this->once())->method('populateWithArray')
            ->with($layoutScheduledStructureData);
        $data = [
            'pageConfigStructure' => $pageConfigStructureData,
            'scheduledStructure' => $layoutScheduledStructureData
        ];

        $this->cacheMock->expects($this->once())
            ->method('load')
            ->with('structure_' . $layoutCacheId)
            ->willReturn(json_encode($data));

        $this->readerPoolMock->expects($this->never())
            ->method('interpret');
        $this->cacheMock->expects($this->never())
            ->method('save');

        $generatorContextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->generatorContextFactoryMock->expects($this->once())
            ->method('create')
            ->with(['structure' => $this->structureMock, 'layout' => $this->model])
            ->willReturn($generatorContextMock);

        $this->generatorPoolMock->expects($this->once())
            ->method('process')
            ->with($this->readerContextMock, $generatorContextMock)
            ->willReturn(true);

        $elements = [
            'name_1' => ['type' => '', 'parent' => null],
            'name_2' => ['type' => Element::TYPE_CONTAINER, 'parent' => null],
            'name_3' => ['type' => '', 'parent' => 'parent'],
            'name_4' => ['type' => Element::TYPE_CONTAINER, 'parent' => 'parent'],
        ];

        $this->structureMock->expects($this->once())
            ->method('exportElements')
            ->willReturn($elements);

        $this->model->generateElements();
    }

    public function testGetXml()
    {
        $xml = '<layout/>';
        $this->assertSame($xml, Layout::LAYOUT_NODE);
    }

    /**
     * @param mixed $displayValue
     * @dataProvider renderElementDisplayDataProvider
     */
    public function testRenderElementDisplay($displayValue)
    {
        $name = 'test_container';
        $child = 'child_block';
        $children = [$child => true];
        $blockHtml = '<html/>';

        $this->structureMock->expects($this->atLeastOnce())
            ->method('getAttribute')
            ->willReturnMap(
                [
                    [$name, 'display', $displayValue],
                    [$child, 'display', $displayValue],
                    [$child, 'type', Element::TYPE_BLOCK]
                ]
            );

        $this->structureMock->expects($this->atLeastOnce())->method('hasElement')
            ->willReturnMap(
                [
                    [$child, true]
                ]
            );

        $this->structureMock->expects($this->once())
            ->method('getChildren')
            ->with($name)
            ->willReturn($children);

        $block = $this->createMock(AbstractBlock::class);
        $block->expects($this->once())->method('toHtml')->willReturn($blockHtml);

        $renderingOutput = new DataObject();
        $renderingOutput->setData('output', $blockHtml);

        $this->eventManagerMock->expects($this->at(0))
            ->method('dispatch')
            ->with(
                'core_layout_render_element',
                ['element_name' => $child, 'layout' => $this->model, 'transport' => $renderingOutput]
            );
        $this->eventManagerMock->expects($this->at(1))
            ->method('dispatch')
            ->with(
                'core_layout_render_element',
                ['element_name' => $name, 'layout' => $this->model, 'transport' => $renderingOutput]
            );

        $this->model->setBlock($child, $block);
        $this->assertEquals($blockHtml, $this->model->renderElement($name, false));
    }

    /**
     * @param mixed $displayValue
     * @dataProvider renderElementDoNotDisplayDataProvider
     */
    public function testRenderElementDoNotDisplay($displayValue)
    {
        $displayValue = 'false';
        $name = 'test_container';
        $blockHtml = '';

        $this->structureMock->expects($this->atLeastOnce())
            ->method('getAttribute')
            ->willReturnMap([[$name, 'display', $displayValue]]);

        $this->assertEquals($blockHtml, $this->model->renderElement($name, false));
    }

    /**
     * @return array
     */
    public function renderElementDoNotDisplayDataProvider()
    {
        return [
            ['false'],
            ['0'],
            [0]
        ];
    }

    /**
     * @return array
     */
    public function renderElementDisplayDataProvider()
    {
        return [
            [true],
            ['1'],
            [1],
            ['true'],
            [false],
            [null]
        ];
    }
}
