<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Developer\Test\Unit\Model\TemplateEngine\Decorator;

class DebugHintsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @param bool $showBlockHints
     * @dataProvider renderDataProvider
     */
    public function testRender($showBlockHints)
    {
        $subject = $this->createMock(\Magento\Framework\View\TemplateEngineInterface::class);
        $block = $this->getMockBuilder(\Magento\Framework\View\Element\BlockInterface::class)
            ->setMockClassName('TestBlock')
            ->getMock();
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
        $model = new \Magento\Developer\Model\TemplateEngine\Decorator\DebugHints($subject, $showBlockHints);
        $actualResult = $model->render($block, 'template.phtml', ['var' => 'val']);
        $this->assertNotNull($actualResult);
    }

    public function renderDataProvider()
    {
        return ['block hints disabled' => [false], 'block hints enabled' => [true]];
    }
}
