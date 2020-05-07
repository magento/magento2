<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Developer\Test\Unit\Model\TemplateEngine\Decorator;

use Magento\Developer\Model\TemplateEngine\Decorator\DebugHints;
use Magento\Framework\View\Element\BlockInterface;
use Magento\Framework\View\TemplateEngineInterface;
use PHPUnit\Framework\TestCase;

class DebugHintsTest extends TestCase
{
    /**
     * @param bool $showBlockHints
     * @dataProvider renderDataProvider
     */
    public function testRender($showBlockHints)
    {
        $subject = $this->getMockForAbstractClass(TemplateEngineInterface::class);
        $block = $this->getMockBuilder(BlockInterface::class)
            ->setMockClassName('TestBlock')
            ->getMockForAbstractClass();
        $subject->expects(
            $this->once()
        )->method(
            'render'
        )->with(
            $this->identicalTo($block),
            'template.phtml',
            ['var' => 'val']
        )->willReturn(
            '<div id="fixture"/>'
        );
        $model = new DebugHints($subject, $showBlockHints);
        $actualResult = $model->render($block, 'template.phtml', ['var' => 'val']);
        $this->assertNotNull($actualResult);
    }

    /**
     * @return array
     */
    public function renderDataProvider()
    {
        return ['block hints disabled' => [false], 'block hints enabled' => [true]];
    }
}
