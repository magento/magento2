<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Layout integration tests
 *
 * Note that some methods are not covered here, see the \Magento\Framework\View\LayoutDirectivesTest
 *
 * @see \Magento\Framework\View\LayoutDirectivesTest
 */
namespace Magento\Framework\View;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class LayoutTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\View\Layout
     */
    protected $_layout;

    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    protected $layoutFactory;

    protected function setUp()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->layoutFactory = $objectManager->get(\Magento\Framework\View\LayoutFactory::class);
        $this->_layout = $this->layoutFactory->create();
        $objectManager->get(\Magento\Framework\App\Cache\Type\Layout::class)->clean();
    }

    public function testConstructorStructure()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $structure = $objectManager->get(\Magento\Framework\View\Layout\Data\Structure::class);
        $structure->createElement('test.container', []);
        /** @var $layout \Magento\Framework\View\LayoutInterface */
        $layout = $this->layoutFactory->create(['structure' => $structure]);
        $this->assertTrue($layout->hasElement('test.container'));
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoAppArea frontend
     */
    public function testDestructor()
    {
        $this->_layout->addBlock(\Magento\Framework\View\Element\Text::class, 'test');
        $this->assertNotEmpty($this->_layout->getAllBlocks());
        $this->_layout->__destruct();
        $this->assertEmpty($this->_layout->getAllBlocks());
    }

    public function testGetUpdate()
    {
        $this->assertInstanceOf(\Magento\Framework\View\Layout\ProcessorInterface::class, $this->_layout->getUpdate());
    }

    public function testGenerateXml()
    {
        $layoutUtility = new Utility\Layout($this);
        /** @var $layout \Magento\Framework\View\LayoutInterface */
        $layout = $this->getMock(
            \Magento\Framework\View\Layout::class,
            ['getUpdate'],
            $layoutUtility->getLayoutDependencies()
        );
        $merge = $this->getMock(\StdClass::class, ['asSimplexml']);
        $merge->expects(
            $this->once()
        )->method(
            'asSimplexml'
        )->will(
            $this->returnValue(
                simplexml_load_string(
                    '<layout><container name="container1"></container></layout>',
                    \Magento\Framework\View\Layout\Element::class
                )
            )
        );
        $layout->expects($this->once())->method('getUpdate')->will($this->returnValue($merge));
        $this->assertEmpty($layout->getXpath('/layout/container[@name="container1"]'));
        $layout->generateXml();
        $this->assertNotEmpty($layout->getXpath('/layout/container[@name="container1"]'));
    }

    /**
     * A smoke test for generating elements
     *
     * See sophisticated tests at \Magento\Framework\View\LayoutDirectivesTest
     * @see \Magento\Framework\View\LayoutDirectivesTest
     * @magentoAppIsolation enabled
     */
    public function testGenerateGetAllBlocks()
    {
        $this->_layout->setXml(
            simplexml_load_string(
                '<layout>
                <block class="Magento\Framework\View\Element\Text" name="block1">
                    <block class="Magento\Framework\View\Element\Text"/>
                </block>
                <block class="Magento\Framework\View\Element\Text" template="test" ttl="360"/>
                <block class="Magento\Framework\View\Element\Text"/>
            </layout>',
                \Magento\Framework\View\Layout\Element::class
            )
        );
        $this->assertEquals([], $this->_layout->getAllBlocks());
        $this->_layout->generateElements();
        $expected = ['block1', 'block1_schedule_block0', 'schedule_block1', 'schedule_block2'];
        $this->assertSame($expected, array_keys($this->_layout->getAllBlocks()));
        $child = $this->_layout->getBlock('block1_schedule_block0');
        $this->assertSame($this->_layout->getBlock('block1'), $child->getParentBlock());
        $this->assertEquals('test', $this->_layout->getBlock('schedule_block1')->getData('template'));
        $this->assertEquals('360', $this->_layout->getBlock('schedule_block1')->getData('ttl'));
        $this->assertFalse($this->_layout->getBlock('nonexisting'));
    }

    public function testGetElementProperty()
    {
        $name = 'test';
        $this->_layout->addContainer($name, 'Test', ['option1' => 1, 'option2' => 2]);
        $this->assertEquals(
            'Test',
            $this->_layout->getElementProperty($name, \Magento\Framework\View\Layout\Element::CONTAINER_OPT_LABEL)
        );
        $this->assertEquals(
            \Magento\Framework\View\Layout\Element::TYPE_CONTAINER,
            $this->_layout->getElementProperty($name, 'type')
        );
        $this->assertSame(2, $this->_layout->getElementProperty($name, 'option2'));

        $this->_layout->addBlock(\Magento\Framework\View\Element\Text::class, 'text', $name);
        $this->assertEquals(
            \Magento\Framework\View\Layout\Element::TYPE_BLOCK,
            $this->_layout->getElementProperty('text', 'type')
        );
        $this->assertSame(
            ['text' => 'text'],
            $this->_layout->getElementProperty($name, \Magento\Framework\Data\Structure::CHILDREN)
        );
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testIsBlock()
    {
        $this->assertFalse($this->_layout->isBlock('container'));
        $this->assertFalse($this->_layout->isBlock('block'));
        $this->_layout->addContainer('container', 'Container');
        $this->_layout->addBlock(\Magento\Framework\View\Element\Text::class, 'block');
        $this->assertFalse($this->_layout->isBlock('container'));
        $this->assertTrue($this->_layout->isBlock('block'));
    }

    public function testSetUnsetBlock()
    {
        $expectedBlockName = 'block_' . __METHOD__;
        $expectedBlock = $this->_layout->createBlock(\Magento\Framework\View\Element\Text::class);

        $this->_layout->setBlock($expectedBlockName, $expectedBlock);
        $this->assertSame($expectedBlock, $this->_layout->getBlock($expectedBlockName));

        $this->_layout->unsetElement($expectedBlockName);
        $this->assertFalse($this->_layout->getBlock($expectedBlockName));
        $this->assertFalse($this->_layout->hasElement($expectedBlockName));
    }

    /**
     * @dataProvider createBlockDataProvider
     */
    public function testCreateBlock($blockType, $blockName, array $blockData, $expectedName)
    {
        $expectedData = $blockData + ['type' => $blockType];

        $block = $this->_layout->createBlock($blockType, $blockName, ['data' => $blockData]);

        $this->assertRegExp($expectedName, $block->getNameInLayout());
        $this->assertEquals($expectedData, $block->getData());
    }

    public function createBlockDataProvider()
    {
        return [
            'named block' => [\Magento\Framework\View\Element\Template::class,
                'some_block_name_full_class',
                ['type' => \Magento\Framework\View\Element\Template::class, 'is_anonymous' => false],
                '/^some_block_name_full_class$/',
            ],
            'no name block' => [\Magento\Framework\View\Element\Text\ListText::class,
                '',
                ['type' => \Magento\Framework\View\Element\Text\ListText::class, 'key1' => 'value1'],
                '/text\\\\list/',
            ]
        ];
    }

    /**
     * @dataProvider blockNotExistsDataProvider
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testCreateBlockNotExists($name)
    {
        $this->_layout->createBlock($name);
    }

    public function blockNotExistsDataProvider()
    {
        return [[''], ['block_not_exists']];
    }

    public function testAddBlock()
    {
        $this->assertInstanceOf(
            \Magento\Framework\View\Element\Text::class,
            $this->_layout->addBlock(\Magento\Framework\View\Element\Text::class, 'block1')
        );
        $block2 = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Framework\View\Element\Text::class
        );
        $block2->setNameInLayout('block2');
        $this->_layout->addBlock($block2, '', 'block1');

        $this->assertTrue($this->_layout->hasElement('block1'));
        $this->assertTrue($this->_layout->hasElement('block2'));
        $this->assertEquals('block1', $this->_layout->getParentName('block2'));
    }

    /**
     * @magentoAppIsolation enabled
     * @dataProvider addContainerDataProvider()
     */
    public function testAddContainer($htmlTag)
    {
        $this->assertFalse($this->_layout->hasElement('container'));
        $this->_layout->addContainer('container', 'Container', ['htmlTag' => $htmlTag]);
        $this->assertTrue($this->_layout->hasElement('container'));
        $this->assertTrue($this->_layout->isContainer('container'));
        $this->assertEquals($htmlTag, $this->_layout->getElementProperty('container', 'htmlTag'));

        $this->_layout->addContainer('container1', 'Container 1', [], 'container', 'c1');
        $this->assertEquals('container1', $this->_layout->getChildName('container', 'c1'));
    }

    public function addContainerDataProvider()
    {
        return [
            ['dd'],
            ['div'],
            ['dl'],
            ['fieldset'],
            ['header'],
            ['ol'],
            ['p'],
            ['section'],
            ['table'],
            ['tfoot'],
            ['ul']
        ];
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testAddContainerInvalidHtmlTag()
    {
        $msg = 'Html tag "span" is forbidden for usage in containers. ' .
            'Consider to use one of the allowed: dd, div, dl, fieldset, main, header, ' .
            'footer, ol, p, section, table, tfoot, ul, nav.';
        $this->setExpectedException(\Magento\Framework\Exception\LocalizedException::class, $msg);
        $this->_layout->addContainer('container', 'Container', ['htmlTag' => 'span']);
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testGetChildBlock()
    {
        $this->_layout->addContainer('parent', 'Parent');
        $block = $this->_layout->addBlock(
            \Magento\Framework\View\Element\Text::class,
            'block',
            'parent',
            'block_alias'
        );
        $this->_layout->addContainer('container', 'Container', [], 'parent', 'container_alias');
        $this->assertSame($block, $this->_layout->getChildBlock('parent', 'block_alias'));
        $this->assertFalse($this->_layout->getChildBlock('parent', 'container_alias'));
    }

    /**
     * @return \Magento\Framework\View\Layout
     */
    public function testSetChild()
    {
        $this->_layout->addContainer('one', 'One');
        $this->_layout->addContainer('two', 'Two');
        $this->_layout->addContainer('three', 'Three');
        $this->assertSame($this->_layout, $this->_layout->setChild('one', 'two', ''));
        $this->_layout->setChild('one', 'three', 'three_alias');
        $this->assertSame(['two', 'three'], $this->_layout->getChildNames('one'));
        return $this->_layout;
    }

    /**
     * @param \Magento\Framework\View\LayoutInterface $layout
     * @depends testSetChild
     */
    public function testReorderChild(\Magento\Framework\View\LayoutInterface $layout)
    {
        $layout->addContainer('four', 'Four', [], 'one');

        // offset +1
        $layout->reorderChild('one', 'four', 1);
        $this->assertSame(['two', 'four', 'three'], $layout->getChildNames('one'));

        // offset -2
        $layout->reorderChild('one', 'three', 2, false);
        $this->assertSame(['two', 'three', 'four'], $layout->getChildNames('one'));

        // after sibling
        $layout->reorderChild('one', 'two', 'three');
        $this->assertSame(['three', 'two', 'four'], $layout->getChildNames('one'));

        // after everyone
        $layout->reorderChild('one', 'three', '-');
        $this->assertSame(['two', 'four', 'three'], $layout->getChildNames('one'));

        // before sibling
        $layout->reorderChild('one', 'four', 'two', false);
        $this->assertSame(['four', 'two', 'three'], $layout->getChildNames('one'));

        // before everyone
        $layout->reorderChild('one', 'two', '-', false);
        $this->assertSame(['two', 'four', 'three'], $layout->getChildNames('one'));

        //reorder by sibling alias
        $layout->reorderChild('one', 'two', 'three_alias', true);
        $this->assertSame(['four', 'three', 'two'], $layout->getChildNames('one'));
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testGetChildBlocks()
    {
        $this->_layout->addContainer('parent', 'Parent');
        $block1 = $this->_layout->addBlock(\Magento\Framework\View\Element\Text::class, 'block1', 'parent');
        $this->_layout->addContainer('container', 'Container', [], 'parent');
        $block2 = $this->_layout->addBlock(\Magento\Framework\View\Element\Template::class, 'block2', 'parent');
        $this->assertSame(['block1' => $block1, 'block2' => $block2], $this->_layout->getChildBlocks('parent'));
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testAddBlockInvalidType()
    {
        $this->_layout->addBlock('invalid_name', 'child');
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testIsContainer()
    {
        $block = 'block';
        $container = 'container';
        $this->_layout->addBlock(\Magento\Framework\View\Element\Text::class, $block);
        $this->_layout->addContainer($container, 'Container');
        $this->assertFalse($this->_layout->isContainer($block));
        $this->assertTrue($this->_layout->isContainer($container));
        $this->assertFalse($this->_layout->isContainer('invalid_name'));
    }

    public function testIsManipulationAllowed()
    {
        $this->_layout->addBlock(\Magento\Framework\View\Element\Text::class, 'block1');
        $this->_layout->addBlock(\Magento\Framework\View\Element\Text::class, 'block2', 'block1');
        $this->assertFalse($this->_layout->isManipulationAllowed('block1'));
        $this->assertFalse($this->_layout->isManipulationAllowed('block2'));

        $this->_layout->addContainer('container1', 'Container 1');
        $this->_layout->addBlock(\Magento\Framework\View\Element\Text::class, 'block3', 'container1');
        $this->_layout->addContainer('container2', 'Container 2', [], 'container1');
        $this->assertFalse($this->_layout->isManipulationAllowed('container1'));
        $this->assertTrue($this->_layout->isManipulationAllowed('block3'));
        $this->assertTrue($this->_layout->isManipulationAllowed('container2'));
    }

    public function testRenameElement()
    {
        $blockName = 'block';
        $expBlockName = 'block_renamed';
        $containerName = 'container';
        $expContainerName = 'container_renamed';
        $block = $this->_layout->createBlock(\Magento\Framework\View\Element\Text::class, $blockName);
        $this->_layout->addContainer($containerName, 'Container');

        $this->assertEquals($block, $this->_layout->getBlock($blockName));
        $this->_layout->renameElement($blockName, $expBlockName);
        $this->assertEquals($block, $this->_layout->getBlock($expBlockName));

        $this->_layout->hasElement($containerName);
        $this->_layout->renameElement($containerName, $expContainerName);
        $this->_layout->hasElement($expContainerName);
    }

    public function testGetBlock()
    {
        $this->assertFalse($this->_layout->getBlock('test'));
        $block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\View\Layout::class
        )->createBlock(
            \Magento\Framework\View\Element\Text::class
        );
        $this->_layout->setBlock('test', $block);
        $this->assertSame($block, $this->_layout->getBlock('test'));
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testGetParentName()
    {
        $this->_layout->addContainer('one', 'One');
        $this->_layout->addContainer('two', 'Two', [], 'one');
        $this->assertFalse($this->_layout->getParentName('one'));
        $this->assertEquals('one', $this->_layout->getParentName('two'));
    }

    public function testGetElementAlias()
    {
        $this->_layout->addContainer('one', 'One');
        $this->_layout->addContainer('two', 'One', [], 'one', '1');
        $this->assertFalse($this->_layout->getElementAlias('one'));
        $this->assertEquals('1', $this->_layout->getElementAlias('two'));
    }

    /**
     * @covers \Magento\Framework\View\Layout::addOutputElement
     * @covers \Magento\Framework\View\Layout::getOutput
     * @covers \Magento\Framework\View\Layout::removeOutputElement
     */
    public function testGetOutput()
    {
        $blockName = 'block_' . __METHOD__;
        $expectedText = "some_text_for_{$blockName}";

        $block = $this->_layout->addBlock(\Magento\Framework\View\Element\Text::class, $blockName);
        $block->setText($expectedText);

        $this->_layout->addOutputElement($blockName);
        // add the same element twice should not produce output duplicate
        $this->_layout->addOutputElement($blockName);
        $this->assertEquals($expectedText, $this->_layout->getOutput());

        $this->_layout->removeOutputElement($blockName);
        $this->assertEmpty($this->_layout->getOutput());
    }

    public function testGetMessagesBlock()
    {
        $this->assertInstanceOf(\Magento\Framework\View\Element\Messages::class, $this->_layout->getMessagesBlock());
    }

    public function testGetBlockSingleton()
    {
        $block = $this->_layout->getBlockSingleton(\Magento\Framework\View\Element\Text::class);
        $this->assertInstanceOf(\Magento\Framework\View\Element\Text::class, $block);
        $this->assertSame($block, $this->_layout->getBlockSingleton(\Magento\Framework\View\Element\Text::class));
    }

    public function testUpdateContainerAttributes()
    {
        $this->_layout->setXml(
            simplexml_load_file(
                __DIR__ . '/_files/layout/container_attributes.xml',
                \Magento\Framework\View\Layout\Element::class
            )
        );
        $this->_layout->generateElements();
        $result = $this->_layout->renderElement('container1', false);
        $this->assertEquals('<div id="container1-2" class="class12">Test11Test12</div>', $result);
        $result = $this->_layout->renderElement('container2', false);
        $this->assertEquals('<div id="container2-2" class="class22">Test21Test22</div>', $result);
    }

    public function testIsCacheable()
    {
        $this->_layout->setXml(
            simplexml_load_file(
                __DIR__ . '/_files/layout/cacheable.xml',
                \Magento\Framework\View\Layout\Element::class
            )
        );
        $this->_layout->generateElements();
        $this->assertTrue($this->_layout->isCacheable());
    }

    public function testIsNonCacheable()
    {
        $this->_layout->setXml(
            simplexml_load_file(
                __DIR__ . '/_files/layout/non_cacheable.xml',
                \Magento\Framework\View\Layout\Element::class
            )
        );
        $this->_layout->generateElements();
        $this->assertFalse($this->_layout->isCacheable());
    }
}
