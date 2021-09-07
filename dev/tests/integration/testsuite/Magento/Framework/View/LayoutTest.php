<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

/**
 * Layout integration tests
 *
 * Note that some methods are not covered here, see the \Magento\Framework\View\LayoutDirectivesTest
 *
 * @see \Magento\Framework\View\LayoutDirectivesTest
 */
namespace Magento\Framework\View;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Messages;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Text;
use Magento\Framework\View\Element\Text\ListText;
use Magento\Framework\View\Layout\Data\Structure;
use Magento\Framework\View\Layout\Element;
use Magento\Framework\View\Layout\ProcessorInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class LayoutTest extends TestCase
{
    /**
     * @var Layout
     */
    private $layout;

    /**
     * @var LayoutFactory
     */
    private $layoutFactory;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->layoutFactory = $objectManager->get(LayoutFactory::class);
        $this->layout = $this->layoutFactory->create();
        $objectManager->get(\Magento\Framework\App\Cache\Type\Layout::class)->clean();
    }

    /**
     * @return void
     */
    public function testConstructorStructure(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $structure = $objectManager->get(Structure::class);
        $structure->createElement('test.container', []);
        /** @var $layout LayoutInterface */
        $layout = $this->layoutFactory->create(['structure' => $structure]);
        $this->assertTrue($layout->hasElement('test.container'));
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoAppArea frontend
     *
     * @return void
     */
    public function testDestructor(): void
    {
        $this->layout->addBlock(Text::class, 'test');
        $this->assertNotEmpty($this->layout->getAllBlocks());
        $this->layout->__destruct();
        $this->assertEmpty($this->layout->getAllBlocks());
    }

    /**
     * @return void
     */
    public function testGetUpdate(): void
    {
        $this->assertInstanceOf(ProcessorInterface::class, $this->layout->getUpdate());
    }

    /**
     * @return void
     */
    public function testGenerateXml(): void
    {
        $layoutUtility = new Utility\Layout($this);
        /** @var $layout LayoutInterface */
        $layout = $this->getMockBuilder(Layout::class)
            ->onlyMethods(['getUpdate'])
            ->setConstructorArgs($layoutUtility->getLayoutDependencies())
            ->getMock();

        $merge = $this->getMockBuilder(\StdClass::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes()
            ->addMethods(['asSimplexml'])
            ->getMock();

        $merge->expects($this->once())
            ->method('asSimplexml')
            ->willReturn(
                simplexml_load_string(
                    '<layout><container name="container1"></container></layout>',
                    Element::class
                )
            );
        $layout->expects($this->once())->method('getUpdate')->willReturn($merge);
        $this->assertEmpty($layout->getXpath('/layout/container[@name="container1"]'));
        $layout->generateXml();
        $this->assertNotEmpty($layout->getXpath('/layout/container[@name="container1"]'));
    }

    /**
     * A smoke test for generating elements.
     *
     * See sophisticated tests at \Magento\Framework\View\LayoutDirectivesTest
     * @see \Magento\Framework\View\LayoutDirectivesTest
     * @magentoAppIsolation enabled
     *
     * @return void
     */
    public function testGenerateGetAllBlocks(): void
    {
        $this->layout->setXml(
            simplexml_load_string(
                '<layout>
                <block class="Magento\Framework\View\Element\Text" name="block1">
                    <block class="Magento\Framework\View\Element\Text"/>
                </block>
                <block class="Magento\Framework\View\Element\Text" template="test" ttl="360"/>
                <block class="Magento\Framework\View\Element\Text"/>
            </layout>',
                Element::class
            )
        );
        $this->assertEquals([], $this->layout->getAllBlocks());
        $this->layout->generateElements();
        $expected = ['block1', 'block1_schedule_block0', 'schedule_block1', 'schedule_block2'];
        $this->assertSame($expected, array_keys($this->layout->getAllBlocks()));
        $child = $this->layout->getBlock('block1_schedule_block0');
        $this->assertSame($this->layout->getBlock('block1'), $child->getParentBlock());
        $this->assertEquals('test', $this->layout->getBlock('schedule_block1')->getData('template'));
        $this->assertEquals('360', $this->layout->getBlock('schedule_block1')->getData('ttl'));
        $this->assertFalse($this->layout->getBlock('nonexisting'));
    }

    /**
     * @return void
     */
    public function testGetElementProperty(): void
    {
        $name = 'test';
        $this->layout->addContainer($name, 'Test', ['option1' => 1, 'option2' => 2]);
        $this->assertEquals(
            'Test',
            $this->layout->getElementProperty($name, Element::CONTAINER_OPT_LABEL)
        );
        $this->assertEquals(
            Element::TYPE_CONTAINER,
            $this->layout->getElementProperty($name, 'type')
        );
        $this->assertSame(2, $this->layout->getElementProperty($name, 'option2'));

        $this->layout->addBlock(Text::class, 'text', $name);
        $this->assertEquals(
            Element::TYPE_BLOCK,
            $this->layout->getElementProperty('text', 'type')
        );
        $this->assertSame(
            ['text' => 'text'],
            $this->layout->getElementProperty($name, \Magento\Framework\Data\Structure::CHILDREN)
        );
    }

    /**
     * @magentoAppIsolation enabled
     *
     * @return void
     */
    public function testIsBlock(): void
    {
        $this->assertFalse($this->layout->isBlock('container'));
        $this->assertFalse($this->layout->isBlock('block'));
        $this->layout->addContainer('container', 'Container');
        $this->layout->addBlock(Text::class, 'block');
        $this->assertFalse($this->layout->isBlock('container'));
        $this->assertTrue($this->layout->isBlock('block'));
    }

    /**
     * @return void
     */
    public function testSetUnsetBlock(): void
    {
        $expectedBlockName = 'block_' . __METHOD__;
        $expectedBlock = $this->layout->createBlock(Text::class);

        $this->layout->setBlock($expectedBlockName, $expectedBlock);
        $this->assertSame($expectedBlock, $this->layout->getBlock($expectedBlockName));

        $this->layout->unsetElement($expectedBlockName);
        $this->assertFalse($this->layout->getBlock($expectedBlockName));
        $this->assertFalse($this->layout->hasElement($expectedBlockName));
    }

    /**
     * @return void
     * @dataProvider createBlockDataProvider
     */
    public function testCreateBlock($blockType, $blockName, array $blockData, $expectedName): void
    {
        $expectedData = $blockData + ['type' => $blockType];

        $block = $this->layout->createBlock($blockType, $blockName, ['data' => $blockData]);

        $this->assertMatchesRegularExpression($expectedName, $block->getNameInLayout());
        $this->assertEquals($expectedData, $block->getData());
    }

    /**
     * @return array
     */
    public function createBlockDataProvider(): array
    {
        return [
            'named block' => [Template::class,
                'some_block_name_full_class',
                ['type' => Template::class, 'is_anonymous' => false],
                '/^some_block_name_full_class$/',
            ],
            'no name block' => [ListText::class,
                '',
                ['type' => ListText::class, 'key1' => 'value1'],
                '/text\\\\list/',
            ]
        ];
    }

    /**
     * @return void
     * @dataProvider blockNotExistsDataProvider
     */
    public function testCreateBlockNotExists($name): void
    {
        $this->expectException(LocalizedException::class);

        $this->layout->createBlock($name);
    }

    /**
     * @return array
     */
    public function blockNotExistsDataProvider(): array
    {
        return [[''], ['block_not_exists']];
    }

    /**
     * @return void
     */
    public function testAddBlock(): void
    {
        $this->assertInstanceOf(
            Text::class,
            $this->layout->addBlock(Text::class, 'block1')
        );
        $block2 = Bootstrap::getObjectManager()->create(
            Text::class
        );
        $block2->setNameInLayout('block2');
        $this->layout->addBlock($block2, '', 'block1');

        $this->assertTrue($this->layout->hasElement('block1'));
        $this->assertTrue($this->layout->hasElement('block2'));
        $this->assertEquals('block1', $this->layout->getParentName('block2'));
    }

    /**
     * @magentoAppIsolation enabled
     * @return void
     * @dataProvider addContainerDataProvider()
     */
    public function testAddContainer($htmlTag): void
    {
        $this->assertFalse($this->layout->hasElement('container'));
        $this->layout->addContainer('container', 'Container', ['htmlTag' => $htmlTag]);
        $this->assertTrue($this->layout->hasElement('container'));
        $this->assertTrue($this->layout->isContainer('container'));
        $this->assertEquals($htmlTag, $this->layout->getElementProperty('container', 'htmlTag'));

        $this->layout->addContainer('container1', 'Container 1', [], 'container', 'c1');
        $this->assertEquals('container1', $this->layout->getChildName('container', 'c1'));
    }

    /**
     * @return array
     */
    public function addContainerDataProvider(): array
    {
        return [
            ['aside'],
            ['dd'],
            ['div'],
            ['dl'],
            ['fieldset'],
            ['main'],
            ['nav'],
            ['header'],
            ['footer'],
            ['ol'],
            ['p'],
            ['section'],
            ['table'],
            ['tfoot'],
            ['ul'],
            ['article'],
            ['h1'],
            ['h2'],
            ['h3'],
            ['h4'],
            ['h5'],
            ['h6'],
        ];
    }

    /**
     * @magentoAppIsolation enabled
     * @return void
     */
    public function testAddContainerInvalidHtmlTag(): void
    {
        $msg = 'Html tag "span" is forbidden for usage in containers. ' .
            'Consider to use one of the allowed: aside, dd, div, dl, fieldset, main, nav, ' .
            'header, footer, ol, p, section, table, tfoot, ul, article, h1, h2, h3, h4, h5, h6.';
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage($msg);
        $this->layout->addContainer('container', 'Container', ['htmlTag' => 'span']);
    }

    /**
     * @magentoAppIsolation enabled
     * @return void
     */
    public function testGetChildBlock(): void
    {
        $this->layout->addContainer('parent', 'Parent');
        $block = $this->layout->addBlock(
            Text::class,
            'block',
            'parent',
            'block_alias'
        );
        $this->layout->addContainer('container', 'Container', [], 'parent', 'container_alias');
        $this->assertSame($block, $this->layout->getChildBlock('parent', 'block_alias'));
        $this->assertFalse($this->layout->getChildBlock('parent', 'container_alias'));
    }

    /**
     * @return Layout
     */
    public function testSetChild(): Layout
    {
        $this->layout->addContainer('one', 'One');
        $this->layout->addContainer('two', 'Two');
        $this->layout->addContainer('three', 'Three');
        $this->assertSame($this->layout, $this->layout->setChild('one', 'two', ''));
        $this->layout->setChild('one', 'three', 'three_alias');
        $this->assertSame(['two', 'three'], $this->layout->getChildNames('one'));

        return $this->layout;
    }

    /**
     * @param LayoutInterface $layout
     * @depends testSetChild
     * @return void
     */
    public function testReorderChild(LayoutInterface $layout): void
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
     * @return void
     */
    public function testGetChildBlocks(): void
    {
        $this->layout->addContainer('parent', 'Parent');
        $block1 = $this->layout->addBlock(Text::class, 'block1', 'parent');
        $this->layout->addContainer('container', 'Container', [], 'parent');
        $block2 = $this->layout->addBlock(Template::class, 'block2', 'parent');
        $this->assertSame(['block1' => $block1, 'block2' => $block2], $this->layout->getChildBlocks('parent'));
    }

    /**
     * @return void
     */
    public function testAddBlockInvalidType(): void
    {
        $this->expectException(LocalizedException::class);

        $this->layout->addBlock('invalid_name', 'child');
    }

    /**
     * @magentoAppIsolation enabled
     *
     * @return void
     */
    public function testIsContainer(): void
    {
        $block = 'block';
        $container = 'container';
        $this->layout->addBlock(Text::class, $block);
        $this->layout->addContainer($container, 'Container');
        $this->assertFalse($this->layout->isContainer($block));
        $this->assertTrue($this->layout->isContainer($container));
        $this->assertFalse($this->layout->isContainer('invalid_name'));
    }

    /**
     * @return void
     */
    public function testIsManipulationAllowed(): void
    {
        $this->layout->addBlock(Text::class, 'block1');
        $this->layout->addBlock(Text::class, 'block2', 'block1');
        $this->assertFalse($this->layout->isManipulationAllowed('block1'));
        $this->assertFalse($this->layout->isManipulationAllowed('block2'));

        $this->layout->addContainer('container1', 'Container 1');
        $this->layout->addBlock(Text::class, 'block3', 'container1');
        $this->layout->addContainer('container2', 'Container 2', [], 'container1');
        $this->assertFalse($this->layout->isManipulationAllowed('container1'));
        $this->assertTrue($this->layout->isManipulationAllowed('block3'));
        $this->assertTrue($this->layout->isManipulationAllowed('container2'));
    }

    /**
     * @return void
     */
    public function testRenameElement(): void
    {
        $blockName = 'block';
        $expBlockName = 'block_renamed';
        $containerName = 'container';
        $expContainerName = 'container_renamed';
        $block = $this->layout->createBlock(Text::class, $blockName);
        $this->layout->addContainer($containerName, 'Container');

        $this->assertEquals($block, $this->layout->getBlock($blockName));
        $this->layout->renameElement($blockName, $expBlockName);
        $this->assertEquals($block, $this->layout->getBlock($expBlockName));

        $this->layout->hasElement($containerName);
        $this->layout->renameElement($containerName, $expContainerName);
        $this->layout->hasElement($expContainerName);
    }

    /**
     * @return void
     */
    public function testGetBlock(): void
    {
        $this->assertFalse($this->layout->getBlock('test'));
        $block = Bootstrap::getObjectManager()->get(
            Layout::class
        )->createBlock(
            Text::class
        );
        $this->layout->setBlock('test', $block);
        $this->assertSame($block, $this->layout->getBlock('test'));
    }

    /**
     * @magentoAppIsolation enabled
     *
     * @return void
     */
    public function testGetParentName(): void
    {
        $this->layout->addContainer('one', 'One');
        $this->layout->addContainer('two', 'Two', [], 'one');
        $this->assertFalse($this->layout->getParentName('one'));
        $this->assertEquals('one', $this->layout->getParentName('two'));
    }

    /**
     * @return void
     */
    public function testGetElementAlias(): void
    {
        $this->layout->addContainer('one', 'One');
        $this->layout->addContainer('two', 'One', [], 'one', '1');
        $this->assertFalse($this->layout->getElementAlias('one'));
        $this->assertEquals('1', $this->layout->getElementAlias('two'));
    }

    /**
     * @covers \Magento\Framework\View\Layout::addOutputElement
     * @covers \Magento\Framework\View\Layout::getOutput
     * @covers \Magento\Framework\View\Layout::removeOutputElement
     *
     * @return void
     */
    public function testGetOutput(): void
    {
        $blockName = 'block_' . __METHOD__;
        $expectedText = "some_text_for_{$blockName}";

        $block = $this->layout->addBlock(Text::class, $blockName);
        $block->setText($expectedText);

        $this->layout->addOutputElement($blockName);
        // add the same element twice should not produce output duplicate
        $this->layout->addOutputElement($blockName);
        $this->assertEquals($expectedText, $this->layout->getOutput());

        $this->layout->removeOutputElement($blockName);
        $this->assertEmpty($this->layout->getOutput());
    }

    /**
     * @return void
     */
    public function testGetMessagesBlock(): void
    {
        $this->assertInstanceOf(Messages::class, $this->layout->getMessagesBlock());
    }

    /**
     * @return void
     */
    public function testGetBlockSingleton(): void
    {
        $block = $this->layout->getBlockSingleton(Text::class);
        $this->assertInstanceOf(Text::class, $block);
        $this->assertSame($block, $this->layout->getBlockSingleton(Text::class));
    }

    /**
     * @return void
     */
    public function testUpdateContainerAttributes(): void
    {
        $this->layout->setXml(
            simplexml_load_file(
                __DIR__ . '/_files/layout/container_attributes.xml',
                Element::class
            )
        );
        $this->layout->generateElements();
        $result = $this->layout->renderElement('container1', false);
        $this->assertEquals('<div id="container1-2" class="class12">Test11Test12</div>', $result);
        $result = $this->layout->renderElement('container2', false);
        $this->assertEquals('<div id="container2-2" class="class22">Test21Test22</div>', $result);
    }

    /**
     * @return void
     */
    public function testIsCacheable(): void
    {
        $this->layout->setXml(
            simplexml_load_file(
                __DIR__ . '/_files/layout/cacheable.xml',
                Element::class
            )
        );
        $this->layout->generateElements();
        $this->assertTrue($this->layout->isCacheable());
    }

    /**
     * @return void
     */
    public function testIsNonCacheable(): void
    {
        $this->layout->setXml(
            simplexml_load_file(
                __DIR__ . '/_files/layout/non_cacheable.xml',
                Element::class
            )
        );
        $this->layout->generateElements();
        $this->assertFalse($this->layout->isCacheable());
    }
}
