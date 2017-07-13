<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Test\Unit\Block\Widget\Grid\Column\Renderer;

use Magento\Framework\DataObject;

class ConcatTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager  */
    protected $objectManagerHelper;

    /** @var \Magento\Backend\Block\Widget\Grid\Column\Renderer\Concat */
    protected $renderer;

    protected function setUp()
    {
//        $this->markTestSkipped('Test needs to be refactored.');
        $this->objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->renderer = $this->objectManagerHelper->getObject(
            \Magento\Backend\Block\Widget\Grid\Column\Renderer\Concat::class
        );
    }

    /**
     * @return array
     */
    public function typeProvider()
    {
        return [
            ['getGetter', ['getTest', 'getBest']],
            ['getIndex', ['test', 'best', 'nothing']],
        ];
    }

    /**
     * @dataProvider typeProvider
     */
    public function testRender($method, $getters)
    {
        $object = new DataObject(['test' => 'a', 'best' => 'b']);
        $column = $this->createPartialMock(\Magento\Backend\Block\Widget\Grid\Column::class, [$method, 'getSeparator']);
        $column->expects($this->any())
            ->method('getSeparator')
            ->will($this->returnValue('-'));
        $column->expects($this->any())
            ->method($method)
            ->will($this->returnValue($getters));
        if ($method == 'getGetter') {
            $column->expects($this->any())
                ->method('getGetter')
                ->willReturn(['getTest', 'getBest']);
        }
        $this->renderer->setColumn($column);
        $this->assertEquals('a-b', $this->renderer->render($object));
    }
}
