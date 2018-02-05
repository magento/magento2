<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Test\Unit\Block\Widget\Grid\Column\Renderer;

use Magento\Framework\DataObject;

class ConcatTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager  */
    protected $objectManagerHelper;

    /** @var \Magento\Backend\Block\Widget\Grid\Column\Renderer\Concat */
    protected $renderer;

    protected function setUp()
    {
        $this->objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->renderer = $this->objectManagerHelper->getObject(
            'Magento\\Backend\\Block\\Widget\\Grid\\Column\\Renderer\\Concat'
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
        $column = $this->getMockBuilder('Magento\Backend\Block\Widget\Grid\Column')
            ->disableOriginalConstructor()
            ->setMethods([$method, 'getSeparator'])
            ->getMock();
        $column->expects($this->any())
            ->method('getSeparator')
            ->will($this->returnValue('-'));
        $column->expects($this->any())
            ->method($method)
            ->will($this->returnValue($getters));
        $column->expects($this->any())
            ->method('getGetter')
            ->willReturn(['getTest', 'getBest']);
        $this->renderer->setColumn($column);
        $this->assertEquals('a-b', $this->renderer->render($object));
    }
}
