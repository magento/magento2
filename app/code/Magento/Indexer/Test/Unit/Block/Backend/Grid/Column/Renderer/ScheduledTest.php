<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Indexer\Test\Unit\Block\Backend\Grid\Column\Renderer;

use Magento\Backend\Block\Context;
use Magento\Framework\DataObject;
use Magento\Indexer\Block\Backend\Grid\Column\Renderer\Scheduled;
use PHPUnit\Framework\TestCase;

class ScheduledTest extends TestCase
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
        $row = new DataObject();
        $column = new DataObject();
        $context = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $model = new Scheduled($context);
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
