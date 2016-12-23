<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Test\Unit;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class LayoutTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\View\Layout
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $structureMock;

    /**
     * @var \Magento\Framework\View\Layout\ProcessorFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $processorFactoryMock;

    /**
     * @var \Magento\Framework\View\Design\Theme\ResolverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $themeResolverMock;

    /**
     * @var \Magento\Framework\View\Model\Layout\Merge|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $processorMock;

    /**
     * @var \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventManagerMock;

    /**
     * @var \Magento\Framework\View\Layout\Generator\Block|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $generatorBlockMock;

    /**
     * @var \Magento\Framework\View\Layout\Generator\Container|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $generatorContainerMock;

    /**
     * @var \Magento\Framework\Cache\FrontendInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $cacheMock;

    /**
     * @var \Magento\Framework\View\Layout\ReaderPool|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $readerPoolMock;

    /**
     * @var \Magento\Framework\View\Layout\GeneratorPool|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $generatorPoolMock;

    /**
     * @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $messageManagerMock;

    /**
     * @var \Magento\Framework\View\Layout\Reader\ContextFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $readerContextFactoryMock;

    /**
     * @var \Magento\Framework\View\Layout\Reader\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $readerContextMock;

    /**
     * @var \Magento\Framework\View\Page\Config\Structure|\PHPUnit_Framework_MockObject_MockObject
     */
    private $pageConfigStructure;

    /**
     * @var \Magento\Framework\View\Layout\ScheduledStructure|\PHPUnit_Framework_MockObject_MockObject
     */
    private $layoutScheduledSructure;

    /**
     * @var \Magento\Framework\View\Layout\Generator\ContextFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $generatorContextFactoryMock;

    /**
     * @var \Magento\Framework\App\State|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $appStateMock;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $loggerMock;

    /**
     * @var SerializerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $serializer;

    protected function setUp()
    {
        $this->structureMock = $this->getMockBuilder(\Magento\Framework\View\Layout\Data\Structure::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->processorFactoryMock = $this->getMock(
            \Magento\Framework\View\Layout\ProcessorFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->themeResolverMock = $this->getMockForAbstractClass(
            \Magento\Framework\View\Design\Theme\ResolverInterface::class
        );
        $this->processorMock = $this->getMock(\Magento\Framework\View\Model\Layout\Merge::class, [], [], '', false);
        $this->eventManagerMock = $this->getMock(\Magento\Framework\Event\ManagerInterface::class);
        $this->generatorBlockMock = $this->getMockBuilder(\Magento\Framework\View\Layout\Generator\Block::class)
            ->disableOriginalConstructor()->getMock();
        $this->generatorContainerMock = $this->getMockBuilder(\Magento\Framework\View\Layout\Generator\Container::class)
            ->disableOriginalConstructor()->getMock();
        $this->cacheMock = $this->getMockBuilder(\Magento\Framework\Cache\FrontendInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->readerPoolMock = $this->getMockBuilder(\Magento\Framework\View\Layout\ReaderPool::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->messageManagerMock = $this->getMockBuilder(\Magento\Framework\Message\ManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->generatorPoolMock = $this->getMockBuilder(\Magento\Framework\View\Layout\GeneratorPool::class)
            ->disableOriginalConstructor()->getMock();
        $this->generatorPoolMock->expects($this->any())
            ->method('getGenerator')
            ->will(
                $this->returnValueMap([
                    [\Magento\Framework\View\Layout\Generator\Block::TYPE, $this->generatorBlockMock],
                    [\Magento\Framework\View\Layout\Generator\Container::TYPE, $this->generatorContainerMock],
                ])
            );

        $this->readerContextFactoryMock = $this->getMockBuilder(
            \Magento\Framework\View\Layout\Reader\ContextFactory::class
        )->disableOriginalConstructor()->getMock();

        $this->pageConfigStructure = $this->getMockBuilder(\Magento\Framework\View\Page\Config\Structure::class)
            ->setMethods(['__toArray', 'populateWithArray'])
            ->getMock();
        $this->layoutScheduledSructure = $this->getMockBuilder(\Magento\Framework\View\Layout\ScheduledStructure::class)
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

        $this->generatorContextFactoryMock = $this->getMockBuilder(
            \Magento\Framework\View\Layout\Generator\ContextFactory::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->appStateMock = $this->getMockBuilder(\Magento\Framework\App\State::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->loggerMock = $this->getMockBuilder(\Psr\Log\LoggerInterface::class)
            ->getMock();
        $this->serializer = $this->getMock(\Magento\Framework\Serialize\SerializerInterface::class);
        $this->serializer->expects($this->any())->method('serialize')
            ->willReturnCallback(function ($value) {
                return json_encode($value);
            });
        $this->serializer->expects($this->any())->method('unserialize')
            ->willReturnCallback(function ($value) {
                return json_decode($value, true);
            });


        $this->model = new \Magento\Framework\View\Layout(
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
        $blockMock = $this->getMockBuilder(\Magento\Framework\View\Element\AbstractBlock::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->structureMock->expects($this->once())
            ->method('createStructuralElement')
            ->with(
                'blockname',
                \Magento\Framework\View\Layout\Element::TYPE_BLOCK,
                \Magento\Framework\View\Element\AbstractBlock::class
            )->willReturn('blockname');
        $this->generatorBlockMock->expects($this->once())->method('createBlock')->will($this->returnValue($blockMock));

        $this->model->createBlock(\Magento\Framework\View\Element\AbstractBlock::class, 'blockname', []);
        $this->assertInstanceOf(
            \Magento\Framework\View\Element\AbstractBlock::class,
            $this->model->getBlock('blockname')
        );
        $this->assertFalse($this->model->getBlock('not_exist'));
    }

    public function testGetUpdate()
    {
        $themeMock = $this->getMockForAbstractClass(\Magento\Framework\View\Design\ThemeInterface::class);

        $this->themeResolverMock->expects($this->once())
            ->method('get')
            ->will($this->returnValue($themeMock));

        $this->processorFactoryMock->expects($this->once())
            ->method('create')
            ->with(['theme' => $themeMock])
            ->will($this->returnValue($this->processorMock));

        $this->assertEquals($this->processorMock, $this->model->getUpdate());
        $this->assertEquals($this->processorMock, $this->model->getUpdate());
    }

    public function testGenerateXml()
    {
        $themeMock = $this->getMockForAbstractClass(\Magento\Framework\View\Design\ThemeInterface::class);

        $this->themeResolverMock->expects($this->once())
            ->method('get')
            ->will($this->returnValue($themeMock));

        $this->processorFactoryMock->expects($this->once())
            ->method('create')
            ->with(['theme' => $themeMock])
            ->will($this->returnValue($this->processorMock));

        $xmlString = '<?xml version="1.0"?><layout xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">'
            . '<some_update>123</some_update></layout>';
        $xml = simplexml_load_string($xmlString, \Magento\Framework\View\Layout\Element::class);
        $this->processorMock->expects($this->once())
            ->method('asSimplexml')
            ->will($this->returnValue($xml));

        $this->structureMock->expects($this->once())
            ->method('importElements')
            ->with($this->equalTo([]))
            ->will($this->returnSelf());
        $this->assertSame($this->model, $this->model->generateXml());
        $this->assertSame('<some_update>123</some_update>', $this->model->getNode('some_update')->asXml());
    }

    public function testGetChildBlock()
    {
        $customBlockName = 'custom_block';
        $customBlockParentName = 'custom_block_parent';
        $customBlockAlias = 'custom_block_alias';

        $blockMock = $this->getMockBuilder(\Magento\Framework\View\Element\Template::class)
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
            ->willReturn(\Magento\Framework\View\Layout\Element::TYPE_BLOCK);

        $this->model->setBlock($customBlockName, $blockMock);
        $this->assertInstanceOf(
            \Magento\Framework\View\Element\AbstractBlock::class,
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
            ->will($this->returnSelf());
        $this->assertSame($this->model, $this->model->setChild($parentName, $elementName, $alias));
    }

    public function testUnsetChild()
    {
        $parentName = 'parent';
        $alias = 'some_alias';
        $this->structureMock->expects($this->once())
            ->method('unsetChild')
            ->with($this->equalTo($parentName), $this->equalTo($alias))
            ->will($this->returnSelf());
        $this->assertSame($this->model, $this->model->unsetChild($parentName, $alias));
    }

    public function testGetChildNames()
    {
        $parentName = 'parent';
        $childrenArray = ['key1' => 'value1', 'key2' => 'value2'];
        $this->structureMock->expects($this->once())
            ->method('getChildren')
            ->with($this->equalTo($parentName))
            ->will($this->returnValue($childrenArray));
        $this->assertSame(['key1', 'key2'], $this->model->getChildNames($parentName));
    }

    public function testGetChildBlocks()
    {
        $parentName = 'parent';
        $childrenArray = ['block_name' => 'value1'];
        $this->structureMock->expects($this->once())
            ->method('getChildren')
            ->with($this->equalTo($parentName))
            ->will($this->returnValue($childrenArray));

        $blockMock = $this->getMockBuilder(\Magento\Framework\View\Element\AbstractBlock::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->structureMock->expects($this->once())
            ->method('createStructuralElement')
            ->with(
                'block_name',
                \Magento\Framework\View\Layout\Element::TYPE_BLOCK,
                \Magento\Framework\View\Element\AbstractBlock::class
            )->willReturn('block_name');
        $this->generatorBlockMock->expects($this->once())->method('createBlock')->will($this->returnValue($blockMock));

        $this->assertSame(
            $blockMock,
            $this->model->createBlock(\Magento\Framework\View\Element\AbstractBlock::class, 'block_name', [])
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
            ->will($this->returnValue('1'));
        $this->assertSame('1', $this->model->getChildName($parentName, $alias));
    }

    public function testAddToParentGroup()
    {
        $blockName = 'block_name';
        $parentGroup = 'parent_group';
        $this->structureMock->expects($this->once())
            ->method('addToParentGroup')
            ->with($this->equalTo($blockName), $this->equalTo($parentGroup))
            ->will($this->returnSelf());
        $this->assertSame($this->structureMock, $this->model->addToParentGroup($blockName, $parentGroup));
    }

    public function testGetGroupChildNames()
    {
        $blockName = 'block_name';
        $groupName = 'group_name';
        $this->structureMock->expects($this->once())
            ->method('getGroupChildNames')
            ->with($this->equalTo($blockName), $this->equalTo($groupName))
            ->will($this->returnSelf());
        $this->assertSame($this->structureMock, $this->model->getGroupChildNames($blockName, $groupName));
    }

    public function testHasElement()
    {
        $elementName = 'name';
        $this->structureMock->expects($this->once())
            ->method('hasElement')
            ->with($this->equalTo($elementName))
            ->will($this->returnValue(true));
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
            ->will($this->returnValue($result));
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
            ->will($this->returnValue($hasElement));
        if ($hasElement) {
            $this->structureMock->expects($this->once())
                ->method('getAttribute')
                ->with($this->equalTo($elementName), $this->equalTo('type'))
                ->will($this->returnValue($attribute));
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
            ->will($this->returnValue($parentName));
        if ($parentName) {
            $this->structureMock->expects($this->once())
                ->method('hasElement')
                ->with($this->equalTo($parentName))
                ->will($this->returnValue($containerConfig['has_element']));
            if ($containerConfig['has_element']) {
                $this->structureMock->expects($this->once())
                    ->method('getAttribute')
                    ->with($this->equalTo($parentName), $this->equalTo('type'))
                    ->will($this->returnValue($containerConfig['attribute']));
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
        $blockMock = $this->getMockBuilder(\Magento\Framework\View\Element\AbstractBlock::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->assertSame($this->model, $this->model->setBlock($blockName, $blockMock));
        $this->assertSame([$blockName => $blockMock], $this->model->getAllBlocks());
        $this->structureMock->expects($this->once())
            ->method('unsetElement')
            ->with($this->equalTo($blockName))
            ->will($this->returnSelf());
        $this->assertSame($this->model, $this->model->unsetElement($blockName));
        $this->assertSame([], $this->model->getAllBlocks());
    }

    public function testRenameElement()
    {
        $oldName = 'old_name';
        $newName = 'new_name';
        $blockMock = $this->getMockBuilder(\Magento\Framework\View\Element\AbstractBlock::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->structureMock->expects($this->once())
            ->method('renameElement')
            ->with($this->equalTo($oldName), $this->equalTo($newName))
            ->will($this->returnSelf());
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
            ->will($this->returnValue($parentId));
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
            ->will($this->returnValue($parentId));
        $this->structureMock->expects($this->once())
            ->method('getChildAlias')
            ->with($this->equalTo($parentId), $this->equalTo($name))
            ->will($this->returnValue($alias));
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
        $blockMock = $this->getMock($blockInstance, [], [], '', false);
        $this->generatorBlockMock->expects($this->once())->method('createBlock')->will($this->returnValue($blockMock));

        if ($isAbstract) {
            $blockMock->expects($this->any())
                ->method('setLayout')
                ->with($this->equalTo($this->model))
                ->will($this->returnSelf());
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
                'some_type', \Magento\Framework\View\Element\Template::class,
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
     * @param bool $result
     * @dataProvider isCacheableDataProvider
     */
    public function testIsCacheable($xmlString, $result)
    {
        $xml = simplexml_load_string($xmlString, \Magento\Framework\View\Layout\Element::class);
        $this->assertSame($this->model, $this->model->setXml($xml));
        $this->assertSame($result, $this->model->isCacheable());
    }

    /**
     * @return array
     */
    public function isCacheableDataProvider()
    {
        return [
            [
                '<?xml version="1.0"?><layout xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">'
                . '<block></block></layout>',
                true,
            ],
            [
                '<?xml version="1.0"?><layout xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">'
                . '<block cacheable="false"></block></layout>',
                false
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
        /** @var \Magento\Framework\View\Layout\Element $xml */
        $xml = simplexml_load_string('<layout/>', \Magento\Framework\View\Layout\Element::class);
        $this->model->setXml($xml);

        $themeMock = $this->getMockForAbstractClass(\Magento\Framework\View\Design\ThemeInterface::class);
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

        $generatorContextMock = $this->getMockBuilder(\Magento\Framework\View\Layout\Generator\Context::class)
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
            'name_2' => ['type' => \Magento\Framework\View\Layout\Element::TYPE_CONTAINER, 'parent' => null],
            'name_3' => ['type' => '', 'parent' => 'parent'],
            'name_4' => ['type' => \Magento\Framework\View\Layout\Element::TYPE_CONTAINER, 'parent' => 'parent'],
        ];

        $this->structureMock->expects($this->once())
            ->method('exportElements')
            ->willReturn($elements);

        $this->model->generateElements();
    }

    public function testGenerateElementsWithCache()
    {
        $layoutCacheId = 'layout_cache_id';
        /** @var \Magento\Framework\View\Layout\Element $xml */
        $xml = simplexml_load_string('<layout/>', \Magento\Framework\View\Layout\Element::class);
        $this->model->setXml($xml);

        $this->readerContextFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->readerContextMock);
        $themeMock = $this->getMockForAbstractClass(\Magento\Framework\View\Design\ThemeInterface::class);
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

        $generatorContextMock = $this->getMockBuilder(\Magento\Framework\View\Layout\Generator\Context::class)
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
            'name_2' => ['type' => \Magento\Framework\View\Layout\Element::TYPE_CONTAINER, 'parent' => null],
            'name_3' => ['type' => '', 'parent' => 'parent'],
            'name_4' => ['type' => \Magento\Framework\View\Layout\Element::TYPE_CONTAINER, 'parent' => 'parent'],
        ];

        $this->structureMock->expects($this->once())
            ->method('exportElements')
            ->willReturn($elements);

        $this->model->generateElements();
    }

    public function testGetXml()
    {
        $xml = '<layout/>';
        $this->assertSame($xml, \Magento\Framework\View\Layout::LAYOUT_NODE);
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
                    [$child, 'type', \Magento\Framework\View\Layout\Element::TYPE_BLOCK]
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

        $block = $this->getMock(\Magento\Framework\View\Element\AbstractBlock::class, [], [], '', false);
        $block->expects($this->once())->method('toHtml')->willReturn($blockHtml);

        $renderingOutput = new \Magento\Framework\DataObject();
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
