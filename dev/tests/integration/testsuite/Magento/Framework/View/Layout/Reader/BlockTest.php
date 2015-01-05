<?php

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

    private $blockName = 'test.block';
    private $childBlockName = 'test.child.block';

    public function setUp()
    {
        $this->block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Framework\View\Layout\Reader\Block::class
        );
        
        $this->readerContext = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Framework\View\Layout\Reader\Context::class
        );
    }

    public function testInterpretBlockDirective()
    {
        $pageXml = new \Magento\Framework\View\Layout\Element(__DIR__ . '/_files/_layout_update_block.xml', 0, true);
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
            ['group' => 'test.group', 'class' => 'Dummy\Class', 'template' => 'test.phtml', 'ttl' => 3],
            $resultElementData['attributes']
        );
        $this->assertEquals(
            ['test_arg' => ['name' => 'test_arg', 'xsi:type' => 'string', 'value' => 'test-argument-value']],
            $resultElementData['arguments']
        );
        $expectedAction = [
            'setTestAction',
            ['test_action_param' => [
                'name' => 'test_action_param', 'xsi:type' => 'string', 'value' => 'test-action-value']
            ]
        ];
        $this->assertEquals(
            [$expectedAction],
            $resultElementData['actions']
        );

        $this->assertEquals('block', $structure->getStructure()[$this->childBlockName][self::IDX_TYPE]);
        $this->assertEquals($this->blockName, $structure->getStructure()[$this->childBlockName][self::IDX_PARENT]);
    }

    /**
     * @depends testInterpretBlockDirective
     */
    public function testInterpretReferenceBlockDirective()
    {
        $pageXml = new \Magento\Framework\View\Layout\Element(__DIR__ . '/_files/_layout_update_reference.xml', 0, true);
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
            ['test_arg' => ['name' => 'test_arg', 'xsi:type' => 'string', 'value' => 'test-argument-value']],
            $resultElementData['arguments']
        );
        $expectedAction = [
            'setTestAction',
            ['test_action_param' => [
                'name' => 'test_action_param', 'xsi:type' => 'string', 'value' => 'test-action-value']
            ]
        ];
        $this->assertEquals(
            [$expectedAction],
            $resultElementData['actions']
        );
    }
} 
