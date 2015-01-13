<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Core\Model\TemplateEngine\Decorator;

class DebugHintsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param bool $showBlockHints
     * @dataProvider renderDataProvider
     */
    public function testRender($showBlockHints)
    {
        $subject = $this->getMock('Magento\Framework\View\TemplateEngineInterface');
        $block = $this->getMock('Magento\Framework\View\Element\BlockInterface', [], [], 'TestBlock', false);
        $subject->expects(
            $this->once()
        )->method(
            'render'
        )->with(
            $this->identicalTo($block),
            'template.phtml',
            ['var' => 'val']
        )->will(
            $this->returnValue('<div id="fixture"/>')
        );
        $model = new DebugHints($subject, $showBlockHints);
        $actualResult = $model->render($block, 'template.phtml', ['var' => 'val']);
        $this->assertSelectEquals('div > div[title="template.phtml"]', 'template.phtml', 1, $actualResult);
        $this->assertSelectCount('div > div#fixture', 1, $actualResult);
        $this->assertSelectEquals('div > div[title="TestBlock"]', 'TestBlock', (int)$showBlockHints, $actualResult);
    }

    public function renderDataProvider()
    {
        return ['block hints disabled' => [false], 'block hints enabled' => [true]];
    }
}
