<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Test\Unit\Block\Backend\Grid\Column\Renderer;

class ScheduledTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param bool $rowValue
     * @param string $class
     * @param string $text
     * @dataProvider typeProvider
     */
    public function testRender($rowValue, $class, $text)
    {
        $html = '<span class="' . $class . '"><span>' . $text . '</span></span>';
        $row = new \Magento\Framework\DataObject();
        $column = new \Magento\Framework\DataObject();
        $context = $this->getMockBuilder('\Magento\Backend\Block\Context')
            ->disableOriginalConstructor()
            ->getMock();

        $model = new \Magento\Indexer\Block\Backend\Grid\Column\Renderer\Scheduled($context);
        $column->setGetter('getValue');
        $row->setValue($rowValue);
        $model->setColumn($column);

        $result = $model->render($row);
        $this->assertEquals($result, $html);
    }

    /**
     * @return array
     */
    public function typeProvider()
    {
        return [
            [true, 'grid-severity-notice', __('Update by Schedule')],
            [false, 'grid-severity-major', __('Update on Save')],
            ['', 'grid-severity-major', __('Update on Save')],
        ];
    }
}
