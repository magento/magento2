<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $blockFactoryMock;

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

    protected function setUp()
    {
        $this->structureMock = $this->getMockBuilder('Magento\Framework\Data\Structure')
            ->disableOriginalConstructor()
            ->getMock();
        $this->blockFactoryMock = $this->getMockBuilder('Magento\Framework\View\Element\BlockFactory')
            ->setMethods(['createBlock'])
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

        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->model = $objectManagerHelper->getObject(
            'Magento\Framework\View\Layout',
            array(
                'structure' => $this->structureMock,
                'blockFactory' => $this->blockFactoryMock,
                'themeResolver' => $this->themeResolverMock,
                'processorFactory' => $this->processorFactoryMock,
                'appState' => $this->appStateMock,
                'eventManager' => $this->eventManagerMock,
                'scheduledStructure' => $this->schStructureMock
            )
        );
    }

    /**
     * @expectedException \Magento\Framework\Model\Exception
     */
    public function testCreateBlockException()
    {
        $this->model->createBlock('type', 'blockname', array());
    }

    public function testCreateBlockSuccess()
    {
        $blockMock = $this->getMockBuilder('Magento\Framework\View\Element\AbstractBlock')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->blockFactoryMock->expects($this->once())->method('createBlock')->will($this->returnValue($blockMock));

        $this->model->createBlock('type', 'blockname', array());
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
            ->with(array('theme' => $themeMock))
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
            ->with(array('theme' => $themeMock))
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

    /**
     * @param string $parentName
     * @param string $alias
     * @param string $name
     * @param bool $isBlock
     * @dataProvider getChildBlockDataProvider
     */
    public function testGetChildBlock($parentName, $alias, $name, $isBlock)
    {
        $this->structureMock->expects($this->once())
            ->method('getChildId')
            ->with($this->equalTo($parentName), $this->equalTo($alias))
            ->will($this->returnValue($name));
        $this->structureMock->expects($this->once())
            ->method('hasElement')
            ->with($this->equalTo($name))
            ->will($this->returnValue($isBlock));
        if ($isBlock) {
            $this->schStructureMock->expects($this->once())
                ->method('hasElement')
                ->with($this->equalTo($name))
                ->will($this->returnValue($isBlock));
            $this->structureMock->expects($this->once())
                ->method('getAttribute')
                ->with($this->equalTo($name), $this->equalTo('type'))
                ->will($this->returnValue(\Magento\Framework\View\Layout\Element::TYPE_BLOCK));
            $this->prepareGenerateBlock($name);
            $this->assertInstanceOf(
                'Magento\Framework\View\Element\AbstractBlock',
                $this->model->getChildBlock($parentName, $alias)
            );
        } else {
            $this->assertFalse($this->model->getChildBlock($parentName, $alias));
        }
    }

    /**
     * @return array
     */
    public function getChildBlockDataProvider()
    {
        return [
            ['parent_name', 'alias', 'block_name', true],
            ['parent_name', 'alias', 'block_name', false]
        ];
    }

    /**
     * @param string $name
     */
    protected function prepareGenerateBlock($name)
    {
        $blockClass = 'Magento\Framework\View\Element\Template';
        $template = 'file.phtml';
        $ttl = 100;
        $xmlString = '<?xml version="1.0"?><block class="' . $blockClass . '" template="' . $template
            . '" ttl="' . $ttl . '"></block>';
        $xml = simplexml_load_string($xmlString, 'Magento\Framework\View\Layout\Element');
        $elementData = [\Magento\Framework\View\Layout\Element::TYPE_BLOCK, $xml, [], []];
        $this->schStructureMock->expects($this->once())
            ->method('getElement')
            ->with($this->equalTo($name))
            ->will($this->returnValue($elementData));
        $this->schStructureMock->expects($this->once())
            ->method('unsetElement')
            ->with($this->equalTo($name))
            ->will($this->returnSelf());
        $blockMock = $this->getMockBuilder('Magento\Framework\View\Element\Template')
            ->setMethods(['setType', 'setNameInLayout', 'addData', 'setLayout', 'setTemplate', 'setTtl'])
            ->disableOriginalConstructor()
            ->getMock();
        $blockMock->expects($this->once())
            ->method('setTemplate')
            ->with($this->equalTo($template))
            ->will($this->returnSelf());
        $blockMock->expects($this->once())
            ->method('setTtl')
            ->with($this->equalTo($ttl))
            ->will($this->returnSelf());
        $blockMock->expects($this->once())
            ->method('setType')
            ->with($this->equalTo(get_class($blockMock)))
            ->will($this->returnSelf());
        $blockMock->expects($this->once())
            ->method('setNameInLayout')
            ->with($this->equalTo($name))
            ->will($this->returnSelf());
        $blockMock->expects($this->once())
            ->method('addData')
            ->with($this->equalTo([]))
            ->will($this->returnSelf());
        $blockMock->expects($this->once())
            ->method('setLayout')
            ->with($this->equalTo($this->model))
            ->will($this->returnSelf());
        $this->blockFactoryMock->expects($this->once())
            ->method('createBlock')
            ->with($this->equalTo('Magento\Framework\View\Element\Template'), $this->equalTo(['data' => []]))
            ->will($this->returnValue($blockMock));
        $this->eventManagerMock->expects($this->once())
            ->method('dispatch')
            ->with(
                $this->equalTo('core_layout_block_create_after'),
                $this->equalTo(['block' => $blockMock])
            )
            ->will($this->returnSelf());
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
        $this->blockFactoryMock->expects($this->once())->method('createBlock')->will($this->returnValue($blockMock));
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

    public function testGetBlockFactory()
    {
        $this->assertSame($this->blockFactoryMock, $this->model->getBlockFactory());
    }

    public function testIsPrivate()
    {
        $this->assertFalse($this->model->isPrivate());
        $this->assertSame($this->model, $this->model->setIsPrivate(true));
        $this->assertTrue($this->model->isPrivate());
    }

    /**
     * @expectedException \Magento\Framework\Model\Exception
     * @expectedExceptionMessage Invalid block type
     */
    public function testGetBlockSingletonException()
    {
        $this->model->getBlockSingleton(false);
    }

    /**
     * @param array $type
     * @param array $blockInstance
     * @dataProvider getBlockSingletonDataProvider
     */
    public function testGetBlockSingleton($type, $blockInstance, $isAbstract)
    {
        $blockMock = $this->getMock($blockInstance, [], [], '', false);
        $this->blockFactoryMock->expects($this->once())
            ->method('createBlock')
            ->with($this->equalTo($type))
            ->will($this->returnValue($blockMock));
        if ($isAbstract) {
            $blockMock->expects($this->once())
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
            [
                'other_type',
                'stdClass',
                false,
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
            'data' => ['some' => 'data']
        ];
        return [
            'wrong namespace' => [
                $rendererData,
                [
                    'namespace' => 'wrong namespace',
                    'static_type' => 'static_type_value',
                    'dynamic_type' => 'dynamic_type_value',
                ],
                null
            ],
            'wrong static type' => [
                $rendererData,
                [
                    'namespace' => 'namespace_value',
                    'static_type' => 'wrong static type',
                    'dynamic_type' => 'dynamic_type_value',
                ],
                null
            ],
            'wrong dynamic type' => [
                $rendererData,
                [
                    'namespace' => 'namespace_value',
                    'static_type' => 'static_type_value',
                    'dynamic_type' => 'wrong dynamic type',
                ],
                null
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
                ]
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
                true
            ],
            [
                '<?xml version="1.0"?><layout xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">'
                . '<block cacheable="false"></block></layout>',
                false
            ],
        ];
    }
}
