<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View;

/**
 * Class LayoutTest
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
     * @var \Magento\Framework\App\State|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $appStateMock;

    /**
     * @var \Magento\Framework\View\Design\Theme\ResolverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $themeResolverMock;

    /**
     * @var \Magento\Core\Model\Layout\Merge|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $processorMock;

    /**
     * @var \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventManagerMock;

    /**
     * @var \Magento\Framework\View\Layout\ScheduledStructure|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $schStructureMock;

    /**
     * @var \Magento\Framework\View\Layout\Generator\Block|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $generatorBlockMock;

    /**
     * @var \Magento\Framework\View\Layout\Generator\Container|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $generatorContainerMock;

    protected function setUp()
    {
        $this->structureMock = $this->getMockBuilder('Magento\Framework\View\Layout\Data\Structure')
            ->disableOriginalConstructor()
            ->getMock();
        $this->processorFactoryMock = $this->getMock(
            'Magento\Framework\View\Layout\ProcessorFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->appStateMock = $this->getMock('Magento\Framework\App\State', [], [], '', false);
        $this->themeResolverMock = $this->getMockForAbstractClass(
            'Magento\Framework\View\Design\Theme\ResolverInterface'
        );
        $this->processorMock = $this->getMock('Magento\Core\Model\Layout\Merge', [], [], '', false);
        $this->schStructureMock = $this->getMock('Magento\Framework\View\Layout\ScheduledStructure', [], [], '', false);
        $this->eventManagerMock = $this->getMock('Magento\Framework\Event\ManagerInterface');
        $this->generatorBlockMock = $this->getMockBuilder('Magento\Framework\View\Layout\Generator\Block')
            ->disableOriginalConstructor()->getMock();
        $this->generatorContainerMock = $this->getMockBuilder('Magento\Framework\View\Layout\Generator\Container')
            ->disableOriginalConstructor()->getMock();

        $generatorPoolMock = $this->getMockBuilder('Magento\Framework\View\Layout\GeneratorPool')
            ->disableOriginalConstructor()->getMock();
        $generatorPoolMock->expects($this->any())
            ->method('getGenerator')
            ->will(
                $this->returnValueMap([
                    [\Magento\Framework\View\Layout\Generator\Block::TYPE, $this->generatorBlockMock],
                    [\Magento\Framework\View\Layout\Generator\Container::TYPE, $this->generatorContainerMock],
                ])
            );

        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->model = $objectManagerHelper->getObject(
            'Magento\Framework\View\Layout',
            [
                'structure' => $this->structureMock,
                'themeResolver' => $this->themeResolverMock,
                'processorFactory' => $this->processorFactoryMock,
                'appState' => $this->appStateMock,
                'eventManager' => $this->eventManagerMock,
                'scheduledStructure' => $this->schStructureMock,
                'generatorPool' => $generatorPoolMock
            ]
        );
    }

    public function testCreateBlockSuccess()
    {
        $blockMock = $this->getMockBuilder('Magento\Framework\View\Element\AbstractBlock')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->structureMock->expects($this->once())
            ->method('createStructuralElement')
            ->with('blockname', \Magento\Framework\View\Layout\Element::TYPE_BLOCK, 'type')
            ->willReturn('blockname');
        $this->generatorBlockMock->expects($this->once())->method('createBlock')->will($this->returnValue($blockMock));

        $this->model->createBlock('type', 'blockname', []);
        $this->assertInstanceOf('Magento\Framework\View\Element\AbstractBlock', $this->model->getBlock('blockname'));
        $this->assertFalse($this->model->getBlock('not_exist'));
    }

    public function testGetUpdate()
    {
        $themeMock = $this->getMockForAbstractClass('Magento\Framework\View\Design\ThemeInterface');

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
        $themeMock = $this->getMockForAbstractClass('Magento\Framework\View\Design\ThemeInterface');

        $this->themeResolverMock->expects($this->once())
            ->method('get')
            ->will($this->returnValue($themeMock));

        $this->processorFactoryMock->expects($this->once())
            ->method('create')
            ->with(['theme' => $themeMock])
            ->will($this->returnValue($this->processorMock));

        $xmlString = '<?xml version="1.0"?><layout xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">'
            . '<some_update>123</some_update></layout>';
        $xml = simplexml_load_string($xmlString, 'Magento\Framework\View\Layout\Element');
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

        $blockMock = $this->getMockBuilder('Magento\Framework\View\Element\Template')
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
            'Magento\Framework\View\Element\AbstractBlock',
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

        $blockMock = $this->getMockBuilder('Magento\Framework\View\Element\AbstractBlock')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->structureMock->expects($this->once())
            ->method('createStructuralElement')
            ->with('block_name', \Magento\Framework\View\Layout\Element::TYPE_BLOCK, 'type')
            ->willReturn('block_name');
        $this->generatorBlockMock->expects($this->once())->method('createBlock')->will($this->returnValue($blockMock));

        $this->assertSame($blockMock, $this->model->createBlock('type', 'block_name', []));
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
        $blockMock = $this->getMockBuilder('Magento\Framework\View\Element\AbstractBlock')
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
        $blockMock = $this->getMockBuilder('Magento\Framework\View\Element\AbstractBlock')
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
                'some_type',
                'Magento\Framework\View\Element\Template',
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
        $xml = simplexml_load_string($xmlString, 'Magento\Framework\View\Layout\Element');
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
}
