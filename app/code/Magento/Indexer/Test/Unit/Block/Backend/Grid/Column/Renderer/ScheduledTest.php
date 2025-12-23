<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
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
     * @param string $indexer
     * @param bool $rowValue
     * @param string $class
     * @param string $text
     * @dataProvider typeProvider
     */
    public function testRender($indexer, $rowValue, $class, $text)
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
        $row->setIndexerId($indexer);
        $model->setColumn($column);

        $result = $model->render($row);
        $this->assertEquals($result, $html);
    }

    /**
     * @return array
     */
    public static function typeProvider()
    {
        return [
            ['customer_grid', true, 'grid-severity-notice', __('Update by Schedule')],
            ['customer_grid', false, 'grid-severity-major', __('Update on Save')],
            ['customer_grid', '', 'grid-severity-major', __('Update on Save')],
            ['catalog_product_price', true, 'grid-severity-notice', __('Update by Schedule')],
            ['catalog_product_price', false, 'grid-severity-major', __('Update on Save')],
            ['catalog_product_price', '', 'grid-severity-major', __('Update on Save')],
        ];
    }
}
