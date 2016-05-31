<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Layout\Reader;

class BlockTest extends \PHPUnit_Framework_TestCase
{
    const IDX_TYPE = 0;
    const IDX_PARENT = 2;

    /**
     * @var Block
     */
    private $block;

    /**
     * @var Context
     */
    private $readerContext;

    /**
     * @var string
     */
    private $blockName = 'test.block';

    /**
     * @var string
     */
    private $childBlockName = 'test.child.block';

    public function setUp()
    {
        $this->block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Framework\View\Layout\Reader\Block'
        );
        $this->readerContext = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Framework\View\Layout\Reader\Context'
        );
    }

    public function testInterpretBlockDirective()
    {
        $pageXml = new \Magento\Framework\View\Layout\Element(
            __DIR__ . '/_files/_layout_update_block.xml',
            0,
            true
        );
        $parentElement = new \Magento\Framework\View\Layout\Element('<page></page>');

        foreach ($pageXml->xpath('body/block') as $blockElement) {
            $this->assertTrue(in_array($blockElement->getName(), $this->block->getSupportedNodes()));
            $this->block->interpret($this->readerContext, $blockElement, $parentElement);
        }

        $structure = $this->readerContext->getScheduledStructure();
        $this->assertArrayHasKey($this->blockName, $structure->getStructure());
        $this->assertEquals('block', $structure->getStructure()[$this->blockName][self::IDX_TYPE]);

        $resultElementData = $structure->getStructureElementData($this->blockName);

        $this->assertEquals(
            [
                Block::ATTRIBUTE_GROUP => 'test.group',
                Block::ATTRIBUTE_CLASS => 'Dummy\Class',
                Block::ATTRIBUTE_TEMPLATE => 'test.phtml',
                Block::ATTRIBUTE_TTL => 3,
                Block::ATTRIBUTE_DISPLAY => '',
                Block::ATTRIBUTE_ACL => ''
            ],
            $resultElementData['attributes']
        );
        $this->assertEquals(
            ['test_arg' => 'test-argument-value'],
            $resultElementData['arguments']
        );

        $this->assertEquals('block', $structure->getStructure()[$this->childBlockName][self::IDX_TYPE]);
        $this->assertEquals($this->blockName, $structure->getStructure()[$this->childBlockName][self::IDX_PARENT]);
    }

    public function testInterpretReferenceBlockDirective()
    {
        $pageXml = new \Magento\Framework\View\Layout\Element(
            __DIR__ . '/_files/_layout_update_reference.xml',
            0,
            true
        );
        $parentElement = new \Magento\Framework\View\Layout\Element('<page></page>');

        foreach ($pageXml->xpath('body/*') as $element) {
            $this->assertTrue(in_array($element->getName(), $this->block->getSupportedNodes()));
            $this->block->interpret($this->readerContext, $element, $parentElement);
        }

        $structure = $this->readerContext->getScheduledStructure();
        $this->assertArrayHasKey($this->blockName, $structure->getStructure());
        $this->assertEquals('block', $structure->getStructure()[$this->blockName][self::IDX_TYPE]);

        $resultElementData = $structure->getStructureElementData($this->blockName);

        $this->assertEquals(
            ['test_arg' => 'test-argument-value'],
            $resultElementData['arguments']
        );
    }
}
