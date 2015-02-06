<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Block\Backend\Grid\Column\Renderer;

class ScheduledTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param bool $bool
     * @param string $class
     * @param string $text
     * @dataProvider typeProvider
     */
    public function testRender($bool, $class, $text)
    {
        $html = '<span class="' . $class . '"><span>' . $text . '</span></span>';
        $row = new \Magento\Framework\Object();
        $column = new \Magento\Framework\Object();
        $context = $this->getMockBuilder('\Magento\Backend\Block\Context')
            ->disableOriginalConstructor()
            ->getMock();

        $model = new Scheduled($context);
        $column->setGetter('getValue');
        $row->setValue($bool);
        $model->setColumn($column);

        $result = $model->render($row);
        $this->assertEquals($result, $html);
    }

    public function typeProvider()
    {
        return [
            [true, 'grid-severity-notice', __('Update by Schedule')],
            [false, 'grid-severity-major', __('Update on Save')],
            ['', 'grid-severity-major', __('Update on Save')],
        ];
    }

}
